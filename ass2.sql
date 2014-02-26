-- COMP3311 13s2 Assignment 2
-- Written by Nathan Orner, September 2013




-- test with check_all();




-- Q1: ...

create or replace view q1_students(student)
as
select c.student as student from course_enrolments c group by c.student having count(c.course) > 65;

create or replace view Q1(unswid, name)
as
select p.unswid,p.name from People p join q1_students q on (q.student=p.id) order by p.unswid;

-- Q2: ...

-- list of all students
create or replace view q2_students_helper(student)
as
select p.id from People p join Students s on (p.id=s.id);

-- list of all staff
create or replace view q2_staff_helper(student)
as
select p.id from People p join Staff s on (p.id=s.id);


-- nstudents
create or replace view q2_nstudents(nstudents)
as
select count(*) from People p join Students s on (p.id=s.id) except (select count(*) from People p join Staff st on (p.id=st.id));

-- nstaff
create or replace view q2_nstaff(nstaff)
as
select count(*) from People p join Staff s on (p.id=s.id) except (select count(*) from People p join Students st on (p.id=st.id));

-- nboth
create or replace view q2_nboth(nboth)
as
select count(*) from q2_students_helper q join q2_staff_helper q2 on (q.student=q2.student);



create or replace view Q2(nstudents, nstaff, nboth)
as
select * from q2_nstudents, q2_nstaff, q2_nboth;




-- Q3: ...

-- associated id of the LIC
create or replace view q3_lic_id(id)
as
select c.id from staff_roles c where c.name = 'Course Convenor';

-- the most number of courses a staff member teaches
create or replace view q3_max_courses(id)
as
select max(y.num) from (select c.staff, count(c.course) as num from course_staff c join q3_lic_id q on (c.role=q.id) group by c.staff) y;

-- the id of that staff member
create or replace view q3_max_courses_staff(name)
as
select c.staff from course_staff c group by c.staff having count(c.course) = 
(select max(y.num) from (select c.staff, count(c.course) as num from course_staff c join q3_lic_id q on (c.role=q.id) group by c.staff) y);

-- the name of that staff member
create or replace view q3_max_courses_name(name)
as
select p.name from q3_max_courses_staff q join people p on (q.name=p.id);




create or replace view Q3(name, ncourses)
as
select * from q3_max_courses_name,q3_max_courses;



-- Q4: ...


create or replace view q4_id_comp(id)
as
select p.id from programs p where code = '3978';

-- students enrolled in 3978 (comp sci)
create or replace view q4_id_comp_students_all(student,semester)
as
select p.student,p.semester from program_enrolments p join q4_id_comp q on (q.id=p.program);

-- id of SEM 2 2005
create or replace view q4_sem_comp(id)
as
select s.id from semesters s where s.name = 'Sem2 2005';

create or replace view q4_student_ids(id)
as
select q.student from q4_id_comp_students_all q join q4_sem_comp q1 on (q.semester=q1.id);



-----------------------------------

create or replace view Q4a(id)	
as
select p.unswid from people p join q4_student_ids q on (q.id=p.id);

 
-----------------------------------


create or replace view q4_stream_id(id)
as
select id from streams where code = 'SENGA1';


create or replace view q4_stream_partof(id)
as
select s.partof from stream_enrolments s join q4_stream_id q on (q.id=s.stream);

create or replace view q4_stream_sem(id)
as
select s.id from semesters s where s.name = 'Sem2 2005';




------------------------------------------------------------
create or replace view Q4b(id)
as
select p.unswid from program_enrolments pe join q4_stream_partof q on (q.id=pe.id)
join q4_stream_sem q1 on (q1.id=pe.semester)
join people p on (p.id=pe.student);
-----------------------------------------------------------


-- CSE id
create or replace view q4c_cse_id(id)
as
select id from orgunits where name like '%Computer Science and Engineering%';

create or replace view q4c_program_id(id)
as
select p.id from q4c_cse_id q join programs p on (p.offeredby=q.id);  


-- students enrolled in 3978 (comp sci)
create or replace view q4c_id_comp_students_all(student,semester)
as
select p.student,p.semester from program_enrolments p join q4c_program_id q on (q.id=p.program);

-- id of SEM 2 2005
create or replace view q4c_sem_comp(id)
as
select s.id from semesters s where s.name = 'Sem2 2005';

create or replace view q4c_student_ids(id)
as
select q.student from q4c_id_comp_students_all q join q4c_sem_comp q1 on (q.semester=q1.id);



--------------------------------------
create or replace view Q4c(id)	
as
select p.unswid from people p join q4c_student_ids q on (q.id=p.id);
----------------------------------------


-- Q5: ...



create or replace view q5_committee_id(id)
as
select id from orgunit_types where name = 'Committee';


create or replace view q5_committees(id)
as
select o.id from q5_committee_id q join orgunits o on (q.id=o.utype);


create or replace view q5_committees_count(num,facultyof)
as
select count(id) as num,facultyof(id) from q5_committees where facultyof(id) <> id group by facultyof(id);



create or replace view q5_committees_max_count(id)
as
select max(y.num) from (select count(id) as num,facultyof(id) from q5_committees where facultyof(id) <> id group by facultyof(id)
)y;


create or replace view q5_result_faculty_id(id)
as
select q.facultyof from q5_committees_count q join q5_committees_max_count q1 on (q.num=q1.id);




create or replace view Q5(name)
as
select o.name from orgunits o join q5_result_faculty_id q on (o.id=q.id);


-- Q6: ...


create or replace function Q6(integer) returns text
as $$
select p.name from people p where (exists (select p.id from people where id = $1) OR exists (select p.id from people where unswid = $1)) AND (id = $1 OR unswid = $1);
$$ language sql;


-- Q7: ...

create or replace function Q7(text)
	returns table (course text, year integer, term text, convenor text)
as $$


select $1, sem.year, CAST(sem.term as text), p.name
from subjects s join courses c on (c.subject=s.id)
		join course_staff cs on (cs.course=c.id)
		join staff_roles sr on (sr.id=cs.role)
		join semesters sem on (sem.id=c.semester)
		join people p on (cs.staff=p.id)
		  	  
where s.code = $1
and sr.name = 'Course Convenor'


$$ language sql;




-- Q8: ...

-- eg. q8a 3489313 as integer  (unswid in people)
-- find associated id in people
-- eg. 1208918 = x
-- using program_enrolments.code as $1  (program code)
-- get id of program_enrolments
-- eg 532, 6274 as program id --> means 3432 as program code


-- term

CREATE OR REPLACE FUNCTION q8(_sid integer)
 RETURNS SETOF NewTranscriptRecord
 LANGUAGE plpgsql
AS $function$
declare
        rec NewTranscriptRecord;
        UOCtotal integer := 0;
        UOCpassed integer := 0;
        wsum integer := 0;
        wam integer := 0;
        x integer;
begin
        select s.id into x
        from   Students s join People p on (s.id = p.id)
        where  p.unswid = _sid;
	 if (not found) then
                raise EXCEPTION 'Invalid student %',_sid;
        end if;
	 for rec in
                select su.code as code,
                         substr(t.year::text,3,2)||lower(t.term) as term,
                         pr.code as prog,
                         substr(su.name,1,20) as name,
                         e.mark as mark, e.grade as grade, su.uoc as uoc
                from   People p
                         join Students s on (p.id = s.id)
                         join Course_enrolments e on (e.student = s.id)
                         join Courses c on (c.id = e.course)
                         join Subjects su on (c.subject = su.id)
                         join Semesters t on (c.semester = t.id)
			    join program_enrolments pe on (pe.semester=t.id)
			    join programs pr on (pr.id=pe.program)
                where  p.unswid = _sid
		  and pe.student = x
                order  by t.starting, su.code
        loop
                if (rec.grade = 'SY') then
                        UOCpassed := UOCpassed + rec.uoc;
                elsif (rec.mark is not null) then
                        if (rec.grade in ('PT','PC','PS','CR','DN','HD','A','B','C')) then
                                -- only counts towards creditted UOC
                                -- if they passed the course
                                UOCpassed := UOCpassed + rec.uoc;
                        end if;
                        -- we count fails towards the WAM calculation
                        UOCtotal := UOCtotal + rec.uoc;
                        -- weighted sum based on mark and uoc for course
                        wsum := wsum + (rec.mark * rec.uoc);
                        -- don't give UOC if they failed
                       if (rec.grade not in ('PT','PC','PS','CR','DN','HD','A','B','C')) then
                                rec.uoc := 0;
                        end if;

                end if;
                return next rec;
        end loop;
        if (UOCtotal = 0) then
               rec := (null,null,null,'No WAM available',null,null,null);
        else
             wam := wsum / UOCtotal;
             rec := (null,null,null,'Overall WAM',wam,null,UOCpassed);
        end if;
        -- append the last record containing the WAM
        return next rec;
end;
$function$;




-- Q9: ...

-- 1058
-- select definition from acad_object_groups where id = 1058;
-- = COMP2###

-- use regexp_replace('Thomas', '.[mN]a.', 'M')
-- regexp_replace(string text, pattern text, replacement text)

-- patterns tested:
-- select id, definition from acad_object_groups where id = 1058 or id = 1410 or id =1121 or id=2801 or id=2299 or id = 5825 or id = 2564 or id = 3929;

-- 1058, 1121, 1410, 2299, 2564, 2801, 3929, 5825



-- have to account for commas (comma seperated list)
-- put in array and loop through?

CREATE OR REPLACE FUNCTION q9(_acadId integer)
 RETURNS SETOF AcObjRecord
 LANGUAGE plpgsql
AS $function$
declare
        rec AcObjRecord;
        x text;
	 origx text;
	 z text[];
begin
	-- split individual chars into array
	z := (select regexp_split_to_array(a.definition, E'\\s*')
	from acad_object_groups a
	where a.id = _acadId);

	-- store original pattern
	select a.definition into origx
	from acad_object_groups a
	where a.id = _acadId;

	-- change groups of #s into a single %
	select regexp_replace(a.definition,'#{1,}', '%','g') into x
	from acad_object_groups a
	where a.id = _acadId;

	select regexp_replace(x,',', '|','g') into x
	from acad_object_groups a
	where a.id = _acadId;

	if (not found) then
                raise EXCEPTION 'Invalid id %',_acadId;
       end if;

	for rec in 
		-- select x
		select a.gtype as objtype, s.code as object
		from subjects s, acad_object_groups a
		where s.code similar to x	
		and a.id = _acadId
	loop
		if ((x like '%;%') or (x like 'GEN%') or (x like 'ZGEN%') or (x like '%=%')) then
			return;
		end if;
		return next rec;
	end loop;
end;
$function$;
