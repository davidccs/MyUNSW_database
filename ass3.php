<?php
// COMP3311 13s2 Assignment 3
// Functions for assignment Tasks A-E
// Written by Nathan Orner, October 2013

// assumes that defs.php has already been included


// Task A: get members of an academic object group



# go to acad_object_groups, search for given id
# based on gtype, if gtype is say subject --> look in subject_group_members
# look in subject_group_members for subject, given ao_group from  acad_object_groups(id)
# look in subjects, given id from subject_group_members (subject)
# get code from subjects


# 2 options: gtype = enumerated or pattern:
	# do above for enumerated


// E.g. list($type,$codes) = membersOf($db, 111899)
// Inputs:
//  $db = open database handle
//  $groupID = acad_object_group.id value
// Outputs:
//  array(GroupType,array(Codes...))
//  GroupType = "subject"|"stream"|"program"
//  Codes = acad object codes in alphabetical order
//  e.g. array("subject",array("COMP2041","COMP2911"))

function membersOf($db,$groupID)
{

$db = dbConnect("dbname=ass3");

$codesArr = array();	

	$q = "select * from acad_object_groups a where a.id = %d";
	$grp = dbOneTuple($db, mkSQL($q, $groupID));
	#print_r($grp["gdefby"]);
	#print_r("\n");
	
	if ($grp["gdefby"] == "enumerated") {

			#print_r($grp["gtype"]);
			#print_r("\n");

				

			if ($grp["gtype"] == "subject") {
			
				$q = "select sub.code
					from acad_object_groups a
						join subject_group_members s on (a.id=s.ao_group)
						join subjects sub on (sub.id=s.subject)
					where a.id = %d
					or a.parent = %d	
				";


			} elseif ($grp["gtype"] == "stream") {

				$q = "select str.code
					from acad_object_groups a
						join stream_group_members st on (a.id=st.ao_group)
						join streams str on (str.id=st.stream)
					where a.id = %d
					or a.parent = %d	
				";


			} elseif ($grp["gtype"] == "program") {

				$q = "select pro.code
					from acad_object_groups a
						join program_group_members p on (a.id=p.ao_group)
						join programs pro on (pro.id=p.program)
					where a.id = %d
					or a.parent = %d	
				";

			}

		
			$codes = dbQuery($db, mkSQL($q, $groupID, $groupID));
			
			while ($t = dbNext($codes)) {
				#print_r($t);
				#echo "$t[code]\n";
				array_push($codesArr, $t[code]);
			}

			#print_r($codesArr);
			sort($codesArr);
			$a = array($grp["gtype"], $codesArr);
			#print_r($a);
			return $a;
			
			



	} elseif ($grp["gdefby"] == "pattern") {


		$q = "select a.definition 					
			from acad_object_groups a
			where a.id = %d
		";
			
		$codes = dbQuery($db, mkSQL($q, $groupID));
			
		$toFind1 = "GENG";
		$toFind2 = "GEN#";
		$toFind3 = "FREE";
		$toFind4 = "####";
		$toFind5 = "all";
		$toFind6 = "ALL";
		$toFind7 = "\F=";
			


		while ($t = dbNext($codes)) {
			#print_r($t);
			#echo "$t[definition]\n";
			$x = $t[definition];
			
			if ((stripos($x, $toFind1) !== false) or (stripos($x, $toFind2) !== false) 
			or (stripos($x, $toFind3) !== false) or (stripos($x, $toFind4) !== false) or (strpos($x, $toFind5) !== false)
		       or (stripos($x, $toFind7) !== false)) {
			
				$a = array($grp["gtype"], array($x));
				return $a;
			}

				
				
				
			$x = preg_replace('/#{1,}/', '%', $t[definition]);
			$x = preg_replace('/,|;/', '|', $x);
			$x = preg_replace('/{/', '(', $x);
			$x = preg_replace('/}/', ')', $x);
		}

		#print_r($x);
		#print_r("\n");

		

		if ($grp["gtype"] == "subject") {
				
			$q = "select s.code
				from acad_object_groups a, subjects s
				where s.code similar to %s
				and a.id = %d
			";


		} elseif ($grp["gtype"] == "stream") {

			$q = "select st.code
				from acad_object_groups a, streams st
				where st.code similar to %s
				and a.id = %d
			";

		} elseif ($grp["gtype"] == "program") {

			$q = "select p.code
				from acad_object_groups a, programs p
				where p.code similar to %s
				and a.id = %d
			";

		}

		$codes = dbQuery($db, mkSQL($q, $x, $groupID));
			
		while ($t = dbNext($codes)) {
			#print_r($t);
			#echo "$t[code]\n";
			array_push($codesArr, $t[code]);
		}

		#print_r($codesArr);
		sort($codesArr);
		$a = array($grp["gtype"], $codesArr);
		#print_r($a);
		return $a;



	}

	$grp = dbOneTuple($db, mkSQL($q, $groupID));
	return array($grp["gtype"], array("########")); // stub
}


// Task B: check if given object is in a group

// E.g. if (inGroup($db, "COMP3311", 111938)) ...
// Inputs:
//  $db = open database handle
//  $code = code for acad object (program,stream,subject)
//  $groupID = acad_object_group.id value
// Outputs:
//  true/false

function inGroup($db, $code, $groupID)
{

$db = dbConnect("dbname=ass3");


$codesArr2 = array();


	# 2 options: enumerated or pattern

	$q = "select * from acad_object_groups a where a.id = %d";
	$grp = dbOneTuple($db, mkSQL($q, $groupID));
	#print_r($grp["gdefby"]);
	#print_r("\n");

	
	$toFind1 = "GENG";
	$toFind2 = "GEN#";
	$toFind3 = "FREE";
	$toFind4 = "####";
	$toFind5 = "all";
	$toFind6 = "ALL";
	$toFind7 = "\F=";

		
	list($type,$codes) = membersOf($db, $groupID);	
	#print_r($type);
	#print_r("\n");
	#print_r($codes);
	#print_r("\n");

	if (in_array($code, $codes)) {
		
		return true;
	#} elseif ((stripos($codes, $toFind3) !== false) or (stripos($codes, $toFind4) !== false) or (strpos($codes, $toFind5) !== false)) {
		#echo "555\n";

		#if first 3 chars of $code is GEN
		if (substr($code,0,3) == "GEN") {
			#return false;
		} else {
			#return true;
		}

	#} elseif ((stripos($codes, $toFind1) !== false) or (stripos($codes, $toFind2) !== false)) {
		#echo "777\n";
		if (substr($code,0,3) == "GEN") {
			#return true;
		} else {
			#return false;
		}

		
	} else {
		
 		
		$tru = 0;
		# loop through $codes, check each for $toFind#'s
		foreach ($codes as $pat) { 	#pattern
			#print_r($pat);
			#echo "\n";
			
			#toFind 3-6
			if ( (substr($pat,0,4) == $toFind3) or (substr($pat,0,4) == $toFind4) or (substr($pat,0,3) == $toFind5) or (substr($pat,0,3) == $toFind6)) {
				#if first 4 chars are #### and first 3 chars are not GEN, you still have to check the last 4 letters
							
				#if first 3 chars of $code is NOT GEN
				if (substr($code,0,3) != "GEN") {

					#    first 4 chars = ####
					if (substr($pat,0,4) == $toFind4) {
						
						$prefixCode = substr($code,0,4); 	# eg. COMP
						$suffixMatch = substr($pat,4,20); 	# eg. 3###
						$resultCode = $prefixCode.$suffixMatch;
						$resultCode = preg_replace('/#{1,}/', '%', $resultCode);


						if (strlen($code) == 8) {	# subject

							$q = "select s.code
								from subjects s
								where s.code similar to %s
							";
							

						} else if (strlen($code) == 6) {	# stream

							$q = "select st.code
								from streams st
								where st.code similar to %s
								";
							
						} else if (strlen($code) == 4) {	# program

							$q = "select p.code
								from programs p
								where p.code similar to %s
							";

						}
						
							
						$codes2 = dbQuery($db, mkSQL($q, $resultCode));
			
						while ($t = dbNext($codes2)) {
							array_push($codesArr2, $t[code]);
						}

						if (in_array($code, $codesArr2)) {
							$tru = 1;
						} else {
							$tru = 0;
						}




					} else {

						$tru = 1;

					}
				}

			}
			
			#toFind 1-2
			if ((substr($pat,0,4) == $toFind1) or (substr($pat,0,4) == $toFind2)) {
			
				#if first 3 chars of $code is GEN
				if (substr($code,0,3) == "GEN") {
					$tru = 1;
					#if (substr($code,3,1) == "GEN") {	# php cansat GENE8001 11461 3214627 10s2
						
					#}
				}
	
			}

			# ! pattern (except)
			if (substr($pat,0,1) == "!") {
				$testCode = substr($pat,1,9);	# remove ! from start
				
			}
	
		}	

		if ($tru) {
			return true;
		} else {	
			return false;
		}

	}
	
	
	
	

	
	
	return false; // stub
}


// Task C: can a subject be used to satisfy a rule

// E.g. if (canSatisfy($db, "COMP3311", 2449, $alloc, $enr)) ...
// Inputs:
//  $db = open database handle
//  $code = code for acad object (program,stream,subject)
//  $ruleID = rules.id value
//  $ruleAlloc = how much of each rule is completed (see spec)
//  $enr = array(ProgramID,array(StreamIDs...))
// Outputs:

function canSatisfy($db, $code, $ruleID, $enrolment)
{

$db = dbConnect("dbname=ass3");

$facArr = array();

#$subjectList = array();

	#from rule, take ao_group
	#determine if rule is a subject, stream or program by:
	#look up rules(ao_group) in acad_object_groups(id) --> check if pattern or enumerated
	#if pattern, look at definition, if enumerated, look at gtype to determine if look in (subject_groups or ...)
	#eg. i have the definition --> compare this with $code --> if yes, then look at $enrolment
	#$enrolment = program id, one/more stream id


	$q = "select a.gdefby, a.definition, a.gtype, a.id
		from rules r
		join acad_object_groups a on (a.id=r.ao_group)
		where r.id = %d
	";
	
	#print_r($enrolment);
	#echo "\n";

	#acad_object_group of $rule
	$ruleData = dbOneTuple($db, mkSQL($q, $ruleID));
	
	if ($ruleData["gdefby"] == "enumerated") {
	
		
			list($type,$codes) = membersOf($db, $ruleData["id"]);

			if (in_array($code, $codes)) {
				return true;
			} else {
				return false;
			}			

			# if gtype = subject ,eg. acad id = 4064
			# then, get using acad(id) =  subject_group_members(ao_group eg. 4064), get list of subject
			# for each subject in list, go to subjects and get code
			# if $code in_array of subject code list


	} elseif ($ruleData["gdefby"] == "pattern") {

		if (substr($code,0,3) == "GEN") {

			#look up what (ancestor) faculty the GEN $code is
			#if it succeeds the if below, it is because the gen ed $code fits the $rule but it is not neccessarily a gen ed under the right faculty
			#check what faculty is needed for the gen ed $rule  by looking in the $enrolment

			if (inGroup($db, $code, $ruleData["id"])) {
				

				# look up offeredBy under streams for each in array of $enrolment
				foreach ($enrolment[1] as $stream) {
					$q = "select facultyOf(offeredBy) as fac from streams where id = %d";
					$facOfStreamArr = dbOneTuple($db, mkSQL($q, $stream));
					$facOfStream = $facOfStreamArr[fac];					

					#print_r($stream);
					#echo "\n";
					#print_r($facOfStream);
					#echo "\n";

					array_push($facArr, $facOfStream);


				}
				$q = "select facultyOf(offeredBy) as fac from programs where id = %d";
				$facOfProgramArr = dbOneTuple($db, mkSQL($q, $enrolment[0]));
				$facOfProgram = $facOfProgramArr[fac];					
				array_push($facArr, $facOfProgram);

				#print_r($facOfProgramArr);
				#echo "\n";

			 	#print_r($facArr);
				#echo "\n";

				#faculty of $code
				
				$q = "select facultyOf(offeredBy) as fac from subjects where code = %s";
				$facOfCodeArr = dbOneTuple($db, mkSQL($q, $code));
				$facOfCode = $facOfCodeArr[fac];					
				
				#print_r($facOfCode);
				#echo "\n";


				if (in_array($facOfCode, $facArr)) {
					return false;
				} else {
					return true;
				}

			}
				

		} else {
		
			#if $code matches $rule
			
			$def = $ruleData["definition"];
			#print_r($def);
			#echo "\n";
			if (inGroup($db, $code, $ruleData["id"])) {
				echo "55555\n";
				return true;
			}
	
		}




	}
	
	return false; // stub
}


// Task D: determine student progress through a degree

// E.g. $vtrans = progress($db, 3012345, "05s1");
// Inputs:
//  $db = open database handle
//  $stuID = People.unswid value (i.e. unsw student id)
//  $semester = code for semester (e.g. "09s2")
// Outputs:
//  Virtual transcript array (see spec for details)

function progress($db, $stuID, $term)
{

$db = dbConnect("dbname=ass3");

$subjectArr = array();
$arr = array();
$semsAvail = array();
$termArray = array();


$rules = array();
$ruleResults = array();

$sortingTerms = array();
$sortingTerms2 = array();

$begin = 0;
	
	# look up id in people using $stuID
	#eg. 1152950 
	# use this id in course_enrolments
	# get the course id eg. 40662
	# look in courses, use id = 40662, get subject eg. 1862 and semester eg. 160 and mark eg. 57 and grade eg. PS
	# look in subjects using id = subject (eg. 1862)  and get code and name
	# look in semesters and use:
		# substr(t.year::text,3,2)||lower(t.term)
	#stuval in courses???	


	#	use transcript (id, sem) instead


	$q = "select s.code, (substr(sem.year::text,3,2)||lower(sem.term)) as termCode, s.name, c.mark, c.grade, s.uoc as uoc, s.id, sem.starting
 		from people p
		join course_enrolments c on (p.id=c.student)
		join courses co on (c.course=co.id)
		join subjects s on (s.id=co.subject)
		join semesters sem on (sem.id=co.semester)
		where p.id = %d
	";
		

	
		

	$result = dbQuery($db, mkSQL($q, $stuID));
	#print_r($result);		
	
	while ($t = dbNext($result)) {
		list($aaa,$bbb,$ccc,$ddd,$eee,$fff,$ggg, $start) = $t;
		$bbb = preg_replace('/x/', 'd', $bbb);	# 11x1 --> 11d1  temporarily
		$bbb = preg_replace('/s1/', 'q1', $bbb);	# 11s1 --> 11r1  temporarily
		$bbb = preg_replace('/a/', 'r', $bbb);	# 07a1 --> 07s1  temporarily

		$hhh = array($aaa,$bbb,$ccc,$ddd,$eee,$fff,"Fits no requirement. Does not count");
		array_push($subjectArr, $hhh);
		$termArray[$bbb] = $start;
	}
	asort($termArray);
	#print_r($termArray);


	foreach ($subjectArr as $array) {
       	 $sortingTerms[] = $array[1];
    	}

    	array_multisort($sortingTerms,SORT_STRING,$subjectArr);


	
	array_push($subjectArr, array("Overall WAM", 50, 72));


	$q = "select starting, (substr(sem.year::text,3,2)||lower(sem.term)) as termCode from semesters sem where id = %d";
	$termStartArr = dbOneTuple($db, mkSQL($q, $term));
	$termStart = $termStartArr["starting"];
	$termCode = $termStartArr["termcode"];
	#print_r($termStart);
	#echo "\n";



	# find the inital semester
	if (in_array($termStart, $termArray)) {

		$termFound = $termCode;
		#print_r($termCode);
		#echo "\n";
		
	} else {	# find closest term
		
		$first = 0;		
		#$found = 0;
		foreach ($termArray as $key => $value) {	
			if (! $first) {
				$termFound = $key;
				$first++;
			}
			if ($value > $termStart) {
				if ($first == 1) {$begin = 1;}
				break;
			}
			$termFound = $key;
		}

				
		
	}




	

	
	#print_r($termFound);
	#echo "\n";


	

	$termFound = preg_replace('/x/', 'd', $termFound);
	$termFound = preg_replace('/s1/', 'q1', $termFound);
	$termFound = preg_replace('/a/', 'r', $termFound);

	$tempTerm = preg_replace('/x/', 'd', $termCode);
	$tempTerm = preg_replace('/s1/', 'q1', $tempTerm);
	$tempTerm = preg_replace('/a/', 'r', $tempTerm);




	# loop through $subjectArr (returned array) and delete unnceccessary arrays from it
	

	$i = -1;
	foreach ($subjectArr as $subject) {
		$i++;

		
		if (($subjectArr[$i][4] == "FL" or $subjectArr[$i][4] == "AF" or $subjectArr[$i][4] == "UF" or $subjectArr[$i][4] == "DF")) {
			$subjectArr[$i][5] = 0;
		}

		if ($subjectArr[$i][3] == 0 and $subjectArr[$i][4] != "SY") {
			$subjectArr[$i][5] = 0;
		}
		
		if (strcmp($tempTerm, $subject[1]) == 0 or $begin == 1) {
			#print_r($tempTerm);
			#print_r($subject[1]);
			$subjectArr[$i][3] = null;
			$subjectArr[$i][4] = null;
			$subjectArr[$i][5] = null;
		}

		# fill in requirements here
		if ($subjectArr[$i][4] == null) {	# no grade
			$subjectArr[$i][6] = "Incomplete. Does not yet count";
		} else if ($subjectArr[$i][5] == null) {	# no uoc
			$subjectArr[$i][6] = "Failed. Does not count";
		}

		if (strcmp($termFound, $subject[1]) < 0) {
			unset($subjectArr[$i]);
		}

		if ($subjectArr[$i][4] == "T " or $subjectArr[$i][4] == "XE") {
			unset($subjectArr[$i]);
		}
		


	}	

	
	#fix up semester sorting
	$i = -1;
	foreach ($subjectArr as $array) {
		$i++;
		$subjectArr[$i][1] = preg_replace('/d/', 'x', $subjectArr[$i][1]);
		$subjectArr[$i][1] = preg_replace('/q1/', 's1', $subjectArr[$i][1]);
		$subjectArr[$i][1] = preg_replace('/r/', 'a', $subjectArr[$i][1]);
	}






	



	#print_r($termArray);


	# find program of term corresponding to termFound(eg. 06s2) in program_enrolments
	$toFindTermStart = $termArray[$termFound];	
	#print_r($toFindTermStart);
	#echo "\n";


	$q = "select p.program, st.stream
		from program_enrolments p
		join semesters s on (s.id=p.semester)
		join stream_enrolments st on (st.partof=p.id)
		where p.student = %d
		and s.starting = %s
	";



	$programArr = dbOneTuple($db, mkSQL($q, $stuID, $toFindTermStart));
	$program = $programArr[program];
	#print_r($program);
	#echo "\n";
	$stream = $programArr[stream];
	#print_r($stream);
	#echo "\n";








	# each acad_object_group has a rule(s?)
	# there are tables called: program_rules for programs,streams & Subject_prereqs 
	#look up program/stream rules
	#& if student.mark >= 50 (ie PS)

		

	$q = "select r.name, r.type, r.min, r.max, a.id, r.id as id2
		from program_rules p
		join rules r on (r.id=p.rule)
		join acad_object_groups a on (a.id=r.ao_group)
		where p.program = %d
	";

	$q1 = "select r.name, r.type, r.min, r.max, a.id, r.id as id2
		from stream_rules s
		join rules r on (r.id=s.rule)
		join acad_object_groups a on (a.id=r.ao_group)
		where s.stream = %d
	";



	$rulesQp = dbQuery($db, mkSQL($q, $program));	
	$rulesQs = dbQuery($db, mkSQL($q1, $stream));
	
	while ($t = dbNext($rulesQp)) {
		#print_r($t);
		#echo "\n";

			if ($t[type] == "CC") {
				$typeEquiv = 0;
			} elseif ($t[type] == "PE") {
				$typeEquiv = 1;
			} elseif ($t[type] == "FE") {
				$typeEquiv = 2;
			} elseif ($t[type] == "GE") {
				$typeEquiv = 3;
			} elseif ($t[type] == "LR") {
				$typeEquiv = 4;	
			} else {
				$typeEquiv = 10;
			}


		array_push($rules, array($t[name], $t[type], $t[min], $t[max], $t[id], $t[id2], $typeEquiv, 0));
	}
	while ($t = dbNext($rulesQs)) {
		#print_r($t);
		#echo "\n";

			if ($t[type] == "CC") {
				$typeEquiv = 0;
			} elseif ($t[type] == "PE") {
				$typeEquiv = 1;
			} elseif ($t[type] == "FE") {
				$typeEquiv = 2;
			} elseif ($t[type] == "GE") {
				$typeEquiv = 3;
			} elseif ($t[type] == "LR") {
				$typeEquiv = 4;	
			} else {
				$typeEquiv = 10;
			}


		array_push($rules, array($t[name], $t[type], $t[min], $t[max], $t[id], $t[id2], $typeEquiv, 0));
	}

	#print_r($rules);


	
	foreach ($rules as $array) {
       	 $sortingTerms2[] = $array[6];
    	}

    	array_multisort($sortingTerms2,SORT_NUMERIC,$rules);



	#print_r($rules);
	#print_r($subjectArr);



	$passingGrades = array('HD', 'DN', 'CR', 'PS', 'PC', 'SY', 'A', 'B', 'C', 'T', 'XE');


	# check that rules have been fulfilled
	# loop through subjects done, loop through rules and check if it fulfills any rule - if it does, break;

	$totalWam = 0;
	$totalUoc = 0;
	$subjectCount = 0;

	$i = -1;
	foreach ($subjectArr as $arraySubject) {
		$i++;
		#print_r($arraySubject); 
		
		$j = -1;
		foreach ($rules as $arrayRule) {
			$j++;

			if (inGroup($db, $arraySubject[0], $arrayRule[4])) {
				#print_r($arraySubject[0]);
				#echo "\n";
				#print_r($arrayRule[0]);
				#echo "\n";

				#alter $subjectArr[$i][6] - add which rule this subject fulfills
				if ($subjectArr[$i][6] != "Incomplete. Does not yet count" and $subjectArr[$i][6] != "Failed. Does not count") {


					if (in_array($subjectArr[$i][4], $passingGrades)) {	# if they have passed the course
						
						if ($rules[$j][3] != null and (($rules[$j][7] + $subjectArr[$i][5]) <= $rules[$j][3])) {	# if max is not null and adding uoc does not overreach max
							
							$rules[$j][7] += $subjectArr[$i][5];
							$subjectArr[$i][6] = $arrayRule[0];
							break;
						} elseif ($rules[$j][3] == null) {	# no max
							
							$rules[$j][7] += $subjectArr[$i][5];
							$subjectArr[$i][6] = $arrayRule[0];
							break;

						}

					}
				}

				#break;
			}		

		}



		
		$totalWam += $subjectArr[$i][3];
		$totalUoc += $subjectArr[$i][5];
		if ($subjectArr[$i][3] != null) {$subjectCount++;}


	}
	
	
	if ($subjectCount == 0) {
		$wam = null;
		$totalUoc = null;
	} else {
		$wam = $totalWam / $subjectCount;
	}


	
	array_push($subjectArr, array("Overall WAM", $wam, $totalUoc));

	#print_r($subjectCount);
	#print_r($rules);


	
	$ignoreRules = array('DS', 'IR', 'RC', 'MR', 'RQ', 'WM');
	$i = -1;
	foreach ($rules as $arrayRule) {
		$i++;

		if (! in_array($rules[$i][1], $ignoreRules)) {

			if (($rules[$i][2]-$rules[$i][7]) > 0) {
				array_push($subjectArr, array($rules[$i][7]." UOC so far; need ".($rules[$i][2]-$rules[$i][7])." UOC more", $rules[$i][0]));
			}
		}

	}
	

#LR rules??? look in spec

	return $subjectArr; // stub
}


// Task E:

// E.g. $advice = advice($db, 3012345, "05s1");
// Inputs:
//  $db = open database handle
//  $stuID = People.unswid value (i.e. unsw student id)
//  $semester = code for semester (e.g. "09s2")
// Outputs:
//  Advice array (see spec for details)


# $stuID, $term for progress

function advice($db, $studentID, $currTermID, $nextTermID)
{

$db = dbConnect("dbname=ass3");



### similar code as Task D until row of #s

$subjectArr = array();
$arr = array();
$semsAvail = array();
$termArray = array();


$rules = array();
$ruleResults = array();

$sortingTerms = array();
$sortingTerms2 = array();

$begin = 0;

	
	# look up id in people using $stuID
	#eg. 1152950 
	# use this id in course_enrolments
	# get the course id eg. 40662
	# look in courses, use id = 40662, get subject eg. 1862 and semester eg. 160 and mark eg. 57 and grade eg. PS
	# look in subjects using id = subject (eg. 1862)  and get code and name
	# look in semesters and use:
		# substr(t.year::text,3,2)||lower(t.term)
	#stuval in courses???	


	#	use transcript (id, sem) instead


	$q = "select s.code, (substr(sem.year::text,3,2)||lower(sem.term)) as termCode, s.name, c.mark, c.grade, s.uoc as uoc, s.id, sem.starting
 		from people p
		join course_enrolments c on (p.id=c.student)
		join courses co on (c.course=co.id)
		join subjects s on (s.id=co.subject)
		join semesters sem on (sem.id=co.semester)
		where p.id = %d
	";
		

	
		

	$result = dbQuery($db, mkSQL($q, $studentID));
	#print_r($result);		
	
	while ($t = dbNext($result)) {
		list($aaa,$bbb,$ccc,$ddd,$eee,$fff,$ggg, $start) = $t;
		$bbb = preg_replace('/x/', 'd', $bbb);	# 11x1 --> 11d1  temporarily
		$bbb = preg_replace('/s1/', 'q1', $bbb);	# 11s1 --> 11r1  temporarily
		$bbb = preg_replace('/a/', 'r', $bbb);	# 07a1 --> 07s1  temporarily

		$hhh = array($aaa,$bbb,$ccc,$ddd,$eee,$fff,"Fits no requirement. Does not count");
		array_push($subjectArr, $hhh);
		$termArray[$bbb] = $start;
	}
	asort($termArray);
	#print_r($termArray);


	foreach ($subjectArr as $array) {
       	 $sortingTerms[] = $array[1];
    	}

    	array_multisort($sortingTerms,SORT_STRING,$subjectArr);


	
	array_push($subjectArr, array("Overall WAM", 50, 72));


	$q = "select starting, (substr(sem.year::text,3,2)||lower(sem.term)) as termCode from semesters sem where id = %d";
	$termStartArr = dbOneTuple($db, mkSQL($q, $currTermID));
	$termStart = $termStartArr["starting"];
	$termCode = $termStartArr["termcode"];
	#print_r($termStart);
	#echo "\n";



	# find the inital semester
	if (in_array($termStart, $termArray)) {

		$termFound = $termCode;
		#print_r($termCode);
		#echo "\n";
		
	} else {	# find closest term
		
		$first = 0;		
		#$found = 0;
		foreach ($termArray as $key => $value) {	
			if (! $first) {
				$termFound = $key;
				$first++;
			}
			if ($value > $termStart) {
				if ($first == 1) {$begin = 1;}
				break;
			}
			$termFound = $key;
		}

				
		
	}




	

	
	#print_r($termFound);
	#echo "\n";


	

	$termFound = preg_replace('/x/', 'd', $termFound);
	$termFound = preg_replace('/s1/', 'q1', $termFound);
	$termFound = preg_replace('/a/', 'r', $termFound);

	$tempTerm = preg_replace('/x/', 'd', $termCode);
	$tempTerm = preg_replace('/s1/', 'q1', $tempTerm);
	$tempTerm = preg_replace('/a/', 'r', $tempTerm);




	# loop through $subjectArr (returned array) and delete unnceccessary arrays from it
	

	$i = -1;
	foreach ($subjectArr as $subject) {
		$i++;

		
		if (($subjectArr[$i][4] == "FL" or $subjectArr[$i][4] == "AF" or $subjectArr[$i][4] == "UF" or $subjectArr[$i][4] == "DF")) {
			$subjectArr[$i][5] = 0;
		}

		if ($subjectArr[$i][3] == 0 and $subjectArr[$i][4] != "SY") {
			$subjectArr[$i][5] = 0;
		}
		
		if (strcmp($tempTerm, $subject[1]) == 0 or $begin == 1) {
			#print_r($tempTerm);
			#print_r($subject[1]);
			$subjectArr[$i][3] = null;
			$subjectArr[$i][4] = null;
			$subjectArr[$i][5] = null;
		}

		# fill in requirements here
		if ($subjectArr[$i][4] == null) {	# no grade
			$subjectArr[$i][6] = "Incomplete. Does not yet count";
		} else if ($subjectArr[$i][5] == null) {	# no uoc
			$subjectArr[$i][6] = "Failed. Does not count";
		}

		if (strcmp($termFound, $subject[1]) < 0) {
			unset($subjectArr[$i]);
		}

		if ($subjectArr[$i][4] == "T " or $subjectArr[$i][4] == "XE") {
			unset($subjectArr[$i]);
		}
		


	}	

	
	#fix up semester sorting
	$i = -1;
	foreach ($subjectArr as $array) {
		$i++;
		$subjectArr[$i][1] = preg_replace('/d/', 'x', $subjectArr[$i][1]);
		$subjectArr[$i][1] = preg_replace('/q1/', 's1', $subjectArr[$i][1]);
		$subjectArr[$i][1] = preg_replace('/r/', 'a', $subjectArr[$i][1]);
	}






	



	#print_r($termArray);


	# find program of term corresponding to termFound(eg. 06s2) in program_enrolments
	$toFindTermStart = $termArray[$termFound];	
	#print_r($toFindTermStart);
	#echo "\n";


	$q = "select p.program, st.stream
		from program_enrolments p
		join semesters s on (s.id=p.semester)
		join stream_enrolments st on (st.partof=p.id)
		where p.student = %d
		and s.starting = %s
	";



	$programArr = dbOneTuple($db, mkSQL($q, $studentID, $toFindTermStart));
	$program = $programArr[program];
	#print_r($program);
	#echo "\n";
	$stream = $programArr[stream];
	#print_r($stream);
	#echo "\n";








	# each acad_object_group has a rule(s?)
	# there are tables called: program_rules for programs,streams & Subject_prereqs 
	#look up program/stream rules
	#& if student.mark >= 50 (ie PS)

		

	$q = "select r.name, r.type, r.min, r.max, a.id, r.id as id2
		from program_rules p
		join rules r on (r.id=p.rule)
		join acad_object_groups a on (a.id=r.ao_group)
		where p.program = %d
	";

	$q1 = "select r.name, r.type, r.min, r.max, a.id, r.id as id2
		from stream_rules s
		join rules r on (r.id=s.rule)
		join acad_object_groups a on (a.id=r.ao_group)
		where s.stream = %d
	";



	$rulesQp = dbQuery($db, mkSQL($q, $program));	
	$rulesQs = dbQuery($db, mkSQL($q1, $stream));
	
	while ($t = dbNext($rulesQp)) {
		#print_r($t);
		#echo "\n";

			if ($t[type] == "CC") {
				$typeEquiv = 0;
			} elseif ($t[type] == "PE") {
				$typeEquiv = 1;
			} elseif ($t[type] == "FE") {
				$typeEquiv = 2;
			} elseif ($t[type] == "GE") {
				$typeEquiv = 3;
			} elseif ($t[type] == "LR") {
				$typeEquiv = 4;	
			} else {
				$typeEquiv = 10;
			}


		array_push($rules, array($t[name], $t[type], $t[min], $t[max], $t[id], $t[id2], $typeEquiv, 0));
	}
	while ($t = dbNext($rulesQs)) {
		#print_r($t);
		#echo "\n";

			if ($t[type] == "CC") {
				$typeEquiv = 0;
			} elseif ($t[type] == "PE") {
				$typeEquiv = 1;
			} elseif ($t[type] == "FE") {
				$typeEquiv = 2;
			} elseif ($t[type] == "GE") {
				$typeEquiv = 3;
			} elseif ($t[type] == "LR") {
				$typeEquiv = 4;	
			} else {
				$typeEquiv = 10;
			}


		array_push($rules, array($t[name], $t[type], $t[min], $t[max], $t[id], $t[id2], $typeEquiv, 0));
	}

	#print_r($rules);


	
	foreach ($rules as $array) {
       	 $sortingTerms2[] = $array[6];
    	}

    	array_multisort($sortingTerms2,SORT_NUMERIC,$rules);



	#print_r($rules);
	#print_r($subjectArr);



	$passingGrades = array('HD', 'DN', 'CR', 'PS', 'PC', 'SY', 'A', 'B', 'C', 'T', 'XE');


	# check that rules have been fulfilled
	# loop through subjects done, loop through rules and check if it fulfills any rule - if it does, break;

	$totalWam = 0;
	$totalUoc = 0;
	$subjectCount = 0;

	$i = -1;
	foreach ($subjectArr as $arraySubject) {
		$i++;
		#print_r($arraySubject); 
		
		$j = -1;
		foreach ($rules as $arrayRule) {
			$j++;

			if (inGroup($db, $arraySubject[0], $arrayRule[4])) {
				#print_r($arraySubject[0]);
				#echo "\n";
				#print_r($arrayRule[0]);
				#echo "\n";

				#alter $subjectArr[$i][6] - add which rule this subject fulfills
				if ($subjectArr[$i][6] != "Incomplete. Does not yet count" and $subjectArr[$i][6] != "Failed. Does not count") {


					if (in_array($subjectArr[$i][4], $passingGrades)) {	# if they have passed the course
						
						if ($rules[$j][3] != null and (($rules[$j][7] + $subjectArr[$i][5]) <= $rules[$j][3])) {	# if max is not null and adding uoc does not overreach max
							
							$rules[$j][7] += $subjectArr[$i][5];
							$subjectArr[$i][6] = $arrayRule[0];
							break;
						} elseif ($rules[$j][3] == null) {	# no max
							
							$rules[$j][7] += $subjectArr[$i][5];
							$subjectArr[$i][6] = $arrayRule[0];
							break;

						}

					}
				}

				#break;
			}		

		}



		
		$totalWam += $subjectArr[$i][3];
		$totalUoc += $subjectArr[$i][5];
		if ($subjectArr[$i][3] != null) {$subjectCount++;}


	}
	
	if ($subjectCount == 0) {
		$wam = null;
		$totalUoc = null;
	} else {
		$wam = $totalWam / $subjectCount;
	}


	
	array_push($subjectArr, array("Overall WAM", $wam, $totalUoc));

	#print_r($subjectCount);
	#print_r($rules);


	
	$ignoreRules = array('DS', 'IR', 'RC', 'MR', 'RQ', 'WM');
	$ignoreRules2 = array('Level 3/4 Electives', 'CS elect');
	#$ignoreRules = array('ZZ');
	$i = -1;
	foreach ($rules as $arrayRule) {
		$i++;

		if (! in_array($rules[$i][1], $ignoreRules) and ! in_array($rules[$i][0], $ignoreRules2)) {

			if (($rules[$i][2]-$rules[$i][7]) > 0) {
				array_push($subjectArr, array($rules[$i][7]." UOC so far; need ".($rules[$i][2]-$rules[$i][7])." UOC more", $rules[$i][0], $rules[$i][4], $rules[$i][2], $rules[$i][3], $rules[$i][7]));
				
			}
		}

	}
	
	#print_r($subjectArr);


###############################################################################################################	


	# php advisor 3233283 13s2

$ruleArr = array();
$subjectList = array();
$foundCodesArr = array();
$prereqArr = array();
$tempArray = array();
$origSubjectList = array();
$toReturn = array();

$foundCodes = array();

	$trans = $subjectArr;

	# get all arrays from $vtrans from TO DO onwards (ie the requirements to fulfill)

	$found = 0;
	$i = -1;
	foreach ($trans as $array) {
		$i++;
		#if ((! $found) and ($array[6] != "Incomplete. Does not yet count" and $array[6] != "Failed. Does not count" and $array[6] != "Fits no requirement. Does not count")) {
		if (! $begin)	{ #if the 
			array_push($subjectList, $array[0]);
		}
		if ($found) {
			
			array_push($ruleArr, $array);
		}
		if ($array[0] == "Overall WAM") {
			$found = 1;
		}
	}
	array_pop($subjectList);

	$origSubjectList = $subjectList;

	


	# for each rule, try and find subjects that satisfy it


	$i = -1;
	foreach ($ruleArr as $array) {

		$i++;
		list($type,$codes) = membersOf($db, $array[2]);
		$foundCodes = array();
	  	$z = -1;

		foreach ($codes as $code) {
			$z++;
	
			

			if (substr($code,0,4) == 'FREE') {
								
				if ($ruleArr[$i][4] != null and (($ruleArr[$i][5] + 6) <= $ruleArr[$i][4]) ) {	# if max is not null and adding uoc does not overreach max
					array_push($toReturn, array("Free...", "Free Electives (many", ($ruleArr[$i][4]-$ruleArr[$i][5]), $array[1]));
				} elseif ($ruleArr[$i][4] == null) {	# no max
					array_push($toReturn, array("Free...", "Free Electives (many", ($ruleArr[$i][3]-$ruleArr[$i][5]), $array[1]));		
				} else {	
				}

				continue;
			}
			if (substr($code,0,3) == "GEN") {
				
				if ($ruleArr[$i][4] != null and (($ruleArr[$i][5] + 6) <= $ruleArr[$i][4]) ) {	# if max is not null and adding uoc does not overreach max
					array_push($toReturn, array("GenEd...", "General Education (m", ($ruleArr[$i][4]-$ruleArr[$i][5]), $array[1]));
				} elseif ($ruleArr[$i][4] == null) {	# no max
					array_push($toReturn, array("GenEd...", "General Education (m", ($ruleArr[$i][3]-$ruleArr[$i][5]), $array[1]));		
				} else {	
				}

				
				continue;
			}
			

			# check if already done that subject
			if (in_array($code, $subjectList)) {
				#continue;	# potential code from rule is already done
			} else {
				array_push($foundCodes, $code);

			}
		
						
		}	

		
		
		$z = -1;
		foreach ($foundCodes as $code) {
			$z++;

		
			$q = "select name, uoc from subjects where code = %s";
			$uocCodeQ = dbOneTuple($db, mkSQL($q, $code));
			$uocCode = $uocCodeQ[uoc];
			$nameCode = $uocCodeQ[name];
			
			
			

			$q = "select p.subject, p.rule, a.definition
				from subjects s
				join subject_prereqs	p on (p.subject=s.id)
				join rules r on (r.id=p.rule)
				join acad_object_groups a on (a.id=r.ao_group)
				where s.code = %s
			";

			$prereqQ = dbQuery($db, mkSQL($q, $code));
			$prereqArr = array();

			while ($t = dbNext($prereqQ)) {
				$tempArray = explode(',',$t[definition]);
				$prereqArr = array_merge($prereqArr, $tempArray);
			}

			
			
			
			
			$prereqGood = 0;
			foreach ($prereqArr as $subject) {
				if (in_array($subject,$origSubjectList)) {
					$prereqGood = 1;
				}
			}
			if (count($prereqArr) == 0) {
				$prereqGood = 1;
			}
			

			if ($ruleArr[$i][4] != null and (($ruleArr[$i][5] + $uocCode) <= $ruleArr[$i][4]) and $prereqGood) {	# if max is not null and adding uoc does not overreach max
								
			} elseif ($ruleArr[$i][4] == null and $prereqGood) {	# no max
						
			} else {
				unset($foundCodes[$z]);
			}



			# check if $code is offered in that semester
			$q = "select c.id
				from semesters s
				join courses c on (s.id=c.semester)
				join subjects sub on (sub.id=c.subject)
				where sub.code = %s
				and s.id = %d
			";
		
			$courseExistQ = dbOneTuple($db, mkSQL($q, $code, $nextTermID));
			$courseExist = ( $courseExistQ[id] != null );
			

			

			

			#found a code that fits a rule $array[2] called $foundCode
			if (in_array($code,$foundCodes) and $prereqGood and $courseExist) {
				#print_r($foundCode);
				#echo " - ";

				$foundCodeRule = $array[1];

				#print_r($foundCodeRule);
				#echo "\n";
				array_push($foundCodesArr, $code);
				array_push($subjectList, $code);
				#if ($foundCodeRule != "Level 3/4 Electives") { # didn't test this
					array_push($toReturn, array($code, $nameCode, $uocCode, $foundCodeRule));
				#}
			}
			

	
		
		}

	

	}

	
	

	return $toReturn;

}
?>
