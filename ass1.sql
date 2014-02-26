-- COMP3311 13s2 Assignment 1
-- Schema for the mypics.net photo-sharing site
--
-- Written by Nathan Orner
--
-- Conventions:
-- * all entity table names are plural
-- * most entities have an artifical primary key called "id"
-- * foreign keys are named after the relationship they represent

-- Domains (you may add more)

create domain URLValue as
	varchar(100) check (value like 'http://%');

create domain EmailValue as
	varchar(100) check (value like '%@%.%');

create domain GenderValue as
	varchar(6) check (value in ('male','female'));

create domain GroupModeValue as
	varchar(15) check (value in ('private','by-invitation','by-request'));

create domain ContactListTypeValue as
	varchar(10) check (value in ('friends','family'));

create domain NameValue as varchar(50);

create domain LongNameValue as varchar(100);

create domain RatingValue as 
	integer check (value > 0 and value < 6);
	
create domain VisibilityValue as
	varchar(15) check (value in ('private', 'friends', 'family', 'friends+family', 'public'));

create domain SafetyValue as
	varchar(15) check (value in ('safe', 'moderate', 'restricted'));

create domain ModeValue as
	varchar(15) check (value in ('private', 'by-invitation', 'by-request'));

create domain contactListValue as
	varchar(10) check (value in ('friends','family'));
	
create domain freqValue as
	integer check (value >= 0);
	
create domain orderValue as
	integer check (value > 0);

-- Tables (you must add more)

create table People (
	id          	serial,
	email_address	EmailValue,
	given_names 	NameValue,
	family_name  	NameValue,
	displayed_name 	LongNameValue not null,
	primary key (id)
);
create table Contact_lists (
	id				serial,
	"type"			contactListValue,
	title			text not null,
	"user"			integer not null,
	primary key (id)
);



create table People_member_Contact_lists (
	person			integer,
	contact_list	integer not null,
	primary key (person,contact_list)
);



create table Users (
    id           		integer references People(id),
	portrait			integer,
	birthday     		date,
	password     		text,
	gender       		GenderValue,
	website      		URLValue,
	date_registered     date,
	primary key (id)
);



create table Groups (
	id				serial,
	"mode"			ModeValue,
	title			text,
	"user"			integer not null,
	primary key (id)
);




create table Users_member_Groups (
	"group"			integer not null,
	"user"			integer,
	primary key ("user","group")
);



create table Photos (
		id          		serial,
        title       		NameValue not null,
        description 		text,
        date_taken   		date,
        date_uploaded  		date,
        file_size    		integer,
        visibility  		VisibilityValue,
        safety_level 		SafetyValue,
        technical_details 	text,
		"user"				integer not null,
		discussion			integer,
		primary key (id)
);



create table Tags (
        id           serial,
        freq         freqValue,
        name         NameValue,
        primary key (id)      
);

create table Users_Photos_has_Tags (
        when_tagged  	timestamp not null,
        tag        		integer not null,
        photo     		integer,
        "user"       	integer,
        primary key (tag,photo,"user")
);



create table Users_rates_Photos (
        rating       RatingValue,
        when_rated   date,
        photo     	 integer,
        "user"       integer,
        primary key (photo,"user")
);



create table Collections (
	id				serial,
	title			NameValue not null,
	description		text,
	photo			integer not null,
	primary key (id)
);



create table User_Collections (
	"user"			integer not null,
	id				integer references Collections(id)
);



create table Group_Collections (
	"group"			integer not null,
	id				integer references Collections(id)
);



create table Photos_in_Collections (
	"order"			orderValue,
	photo			integer,
	collection		integer,
	primary key (photo,collection)
);



create table Discussions (
	id			serial,
	title		NameValue,
	primary key (id)
);

create table Comments (
	id				serial,
	when_posted		timestamp not null,
	content			text,
	discussion		integer not null,
	"user"			integer not null,
	reply			integer,
	foreign key (reply) references Comments(id),
	primary key (id)
);

create table Groups_has_Discussions (
	"group"			integer,
	discussion		integer,
	primary key ("group",discussion),
	foreign key ("group") references Groups(id),
	foreign key (discussion) references Discussions(id)
);

alter table Contact_lists add foreign key ("user") references Users(id);
alter table People_member_Contact_lists add foreign key (person) references People(id);
alter table People_member_Contact_lists add foreign key (contact_list) references Contact_lists(id);
alter table Users add foreign key (portrait) references Photos(id);
alter table Groups add foreign key ("user") references Users(id);
alter table Users_member_Groups add foreign key ("group") references Groups(id);
alter table Users_member_Groups add foreign key ("user") references Users(id);
alter table Photos add foreign key ("user") references Users(id);
alter table Users_Photos_has_Tags add foreign key (tag) references Tags(id);
alter table Users_Photos_has_Tags add foreign key (photo) references Photos(id);
alter table Users_Photos_has_Tags add foreign key ("user") references Users(id);
alter table Users_rates_Photos add foreign key (photo) references Photos(id);
alter table Users_rates_Photos add foreign key ("user") references Users(id);
alter table Collections add foreign key (photo) references Photos(id);
alter table User_Collections add foreign key ("user") references Users(id);
alter table Group_Collections add foreign key ("group") references Groups(id);
alter table Photos_in_Collections add foreign key (photo) references Photos(id);
alter table Photos_in_Collections add foreign key (collection) references Collections(id);
alter table Comments add foreign key (discussion) references Discussions(id);
alter table Comments add foreign key ("user") references Users(id);

alter table Photos add foreign key (discussion) references Discussions(id);


alter table User_Collections add primary key (id);
alter table Group_Collections add primary key (id);