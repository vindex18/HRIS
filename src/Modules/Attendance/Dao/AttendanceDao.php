<?php

namespace App\Modules\Attendance\Dao;
use App\Modules\Attendance\Models\AttendanceModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use \DateTime;

class AttendanceDao {
    function __construct(){ 
        
    }

    function addAttendance($type_id, $emp_id, $datetime){
        
    }

    function getDistinctDateAttendanceOfEmployee($from, $to, $emp_id){
        //DB::connection()->enableQueryLog();
        return DB::table('attendance as a')
                        ->select(DB::raw('DISTINCT(DATE(datetime)) as date'))
                        ->where('emp_id', $emp_id)
                        ->whereBetween('datetime', [$from, $to])
                        ->orderBy('datetime', 'ASC')
                        ->get()
                        ->toArray();

        //$queries = DB::getQueryLog(); var_dump($queries); die("End of Query");
    }

    function getEmployeeAttendance($from, $to, $emp_id){
        $distnctdate = AttendanceDao::getDistinctDateAttendanceOfEmployee($from, $to, $emp_id);

        for($a=0;$a<count($distnctdate);$a++){
            //DB::connection()->enableQueryLog();
            $data[$a] = DB::table('employees AS e')
                        ->select('e.first_name', 'e.last_name', 'e.is_active', 'e.pos_title', 'a.datetime', 'a.emp_id', 't.code', 't.description')
                        ->leftJoin('attendance AS a' , 'e.id', '=', 'a.emp_id')
                        ->leftJoin('attendancetype as t', 'a.type_id', '=', 't.id')
                        ->where('e.id', '=', $emp_id)
                        ->whereDate('a.datetime', $distnctdate[$a]->date)
                        ->orderBy('datetime', 'ASC')
                        ->get()
                        ->toArray();
        }
            //$queries = DB::getQueryLog(); var_dump($queries); die("End of Query");    
            //echo strtotime('2018-06-31 00:00:00'); die();
            echo "Employee Name: <b>".$data[0][0]->last_name.", ".$data[0][0]->first_name."</b><br>Position: <b>".$data[0][0]->pos_title."</b><br><br><strong>Attendance Record</strong><br>----------------------------<br><br>****************************<br>";
            for($a=0;$a<count($data);$a++){ //distinct date
                //for($b=0;$b<count($data[$a]);$b++){ //traversing through presets [TI - TO]
                    //check if punch completed
                    echo "<i>".date('M d, Y g:i A', strtotime($data[$a][0]->datetime))."</i><br>";
                    $stat[$a] = AttendanceDao::checkIfPunchComplete($data[$a], $emp_id);
                    //var_dump($stat[$a]); die();
                    //echo $data[$a][$b]->datetime." - ".$data[$a][$b]->description." - ".$data[$a][$b]->code."<br>";
                //}
                echo "<br>****************************<br>";
            }
            
            //var_dump($stat); 
            die('----------End of Result-------------');
        
    }

    function checkIfPunchComplete($data, $emp_id){ //Checking One Day if completed
        //Declared to be constant
        //$presets = array('TI', 'BO1', 'BI1', 'LO', 'LI', 'BO2', 'BI2', 'TO');
        //$presets1 = array('Time in', 'Break Out 1', 'Break In 1', 'Lunch Out', 'Lunch In', 'Break Out 2', 'Break In 2', 'Time Out');
        $presets = array('TI', 'TO');
        $presets1 = array('Time in', 'Time Out');
        $datetime = array('', '');
        
        for($a=0;$a<count($presets);$a++){
            for($c=0;$c<count($data);$c++){
                if($presets[$a]==$data[$c]->code)
                {
                    $datetime[$a] = $data[$c]->datetime;
                    if($data[$c]->code=="TI")
                        $last_time_in = $data[$c]->datetime;

                    if($data[$c]->code=="TO")
                        $last_time_out = $data[$c]->datetime;
                }
            }
        }

        //if TI or TO is 0 -> get the next TI else calculate
        if(in_array('', $datetime)){ //*** DEFICIENCY *REVISION ONCE HAS DEFICIENCY DECLARE INCOMPLETE 
            //if($datetime[7]==0){ //No Time Out - REMARKKKKKKKKKKKKKKKKKKKKKKSSSS
                //$next_time_in = AttendanceDao::getNextTimeIn($last_time_out, $emp_id);
            //if($datetime[7]==''&&$datetime[0]!='') //No TimeOut but has Time In
             //   $next_time_in = AttendanceDao::getNextTimeIn($last_time_out, $emp_id);
            echo "<u>Incomplete Record</u><br><br>";
            for($c=0;$c<count($datetime);$c++){
                if($datetime[$c]!="")
                    echo date('M d, Y g:i A', strtotime($datetime[$c]))." - ".$presets1[$c]."<br>"; //." - ".$presets[$c]."<br>"
                else
                    echo "No Record&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - ".$presets1[$c]."<br>"; //." - ".$presets[$c]."<br>"
            }
            echo "<br>Total Hours: Incomplete Record";
            return 0;
            //return "Incomplete";
        }
        else //Complete then calculate total hours then check for violation 2 (15 Minutes Break) 1 (1 Hour Lunchbreak)
        {
            //echo "<br>Calculating......<br>";
            echo "<u>Complete Record</u><br><br>";
            $sum = new DateTime('00:00');
            $total = clone $sum;
            
            for($c=0;$c<count($datetime);$c++){
                $f = new DateTime($datetime[$c]);
                echo date('M d, Y g:i A', strtotime($datetime[$c]))." - ".$presets1[$c]."<br>"; //." - ".$presets[$c]."<br>"
                if($c!=3){ //Don't Include Lunch Breaks
                    if(array_key_exists($c+1,$datetime))
                    {
                        $t = new DateTime($datetime[$c+1]);
                        $diff = date_diff($f, $t);
                        //echo $diff->format('%y Years %m Months %d Days %h Hours %i Minutes %s Seconds')."<br>";
                        $diffx[$c] = $diff;
                        $total->add($diffx[$c]);
                    }
                }
            }
            //echo "<br><br>----------TIME DIFF---------------------<br>";
            echo "<br>Total Hours: ".$total->diff($sum)->format('%h Hr %i Min %s Sec'); //%y Years %m Months %d Days 
            return $total->diff($sum)->format('%h Hours %i Minutes %s Seconds');
            //echo "<br>".date('Y-m-j', strtotime('3 weekdays')); die(); I can use this function
            //echo "<br>Total Hours: ".$total->diff($sum)->format('%y Years %m Months %d Days %h Hours %i Minutes %s Seconds');
            
            //Total Amount for break - 30 minutes 
            //Total Amount for Lunchbreak - 1 Hour
             
        } 
     
        //Debugging
        /*echo "<br><br>-----FOR DEBUGGING---------<br>";
        for($c=0;$c<count($datetime);$c++){
            echo $datetime[$c]." - ".$presets[$c]."<br>";
        }*/
    
        die('<br>zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz');
    }

    /*function getEmployeeAttendance($from, $to, $emp_id){ //With Full Type Support
        //Get Distinct?
        //Get the minimum punch >= dtfrom
      
        $distnctdate = AttendanceDao::getDistinctDateAttendanceOfEmployee($from, $to, $emp_id);
        //var_dump($distnct); die();
        for($a=0;$a<count($distnctdate);$a++){
            //DB::connection()->enableQueryLog();
            $data[$a] = DB::table('employees AS e')
                        ->select('e.first_name', 'e.last_name', 'e.is_active', 'e.pos_title', 'a.datetime', 'a.emp_id', 't.code', 't.description')
                        ->leftJoin('attendance AS a' , 'e.id', '=', 'a.emp_id')
                        ->leftJoin('attendancetype as t', 'a.type_id', '=', 't.id')
                        ->where('e.id', '=', $emp_id)
                        ->whereDate('a.datetime', $distnctdate[$a]->date)
                        ->orderBy('datetime', 'ASC')
                        ->get()
                        ->toArray();
            //$queries = DB::getQueryLog(); var_dump($queries); die("End of Query");         
        }
      
        echo "Employee Name: <b>".$data[0][0]->last_name.", ".$data[0][0]->first_name."</b><br>Position: <b>".$data[0][0]->pos_title."</b><br><br><strong>Attendance Record</strong><br>----------------------------<br><br>****************************<br>";

        for($a=0;$a<count($data);$a++){ //distinct date
            //for($b=0;$b<count($data[$a]);$b++){ //traversing through presets [TI - TO]
                //check if punch completed
                echo "<i>".date('M d, Y g:i A', strtotime($data[$a][0]->datetime))."</i><br>";
                $stat[$a] = AttendanceDao::checkIfPunchComplete($data[$a], $emp_id);
                //var_dump($stat[$a]); die();
                //echo $data[$a][$b]->datetime." - ".$data[$a][$b]->description." - ".$data[$a][$b]->code."<br>";
            //}
            echo "<br>****************************<br>";
        }
        
        //var_dump($stat); 
        die('----------End of Result-------------');
    }

    function checkIfPunchComplete($data, $emp_id){ //Checking One Day if completed (WITH FULL TYPE SUPPORT)
        //Declared to be constant
        $presets = array('TI', 'BO1', 'BI1', 'LO', 'LI', 'BO2', 'BI2', 'TO');
        $presets1 = array('Time in', 'Break Out 1', 'Break In 1', 'Lunch Out', 'Lunch In', 'Break Out 2', 'Break In 2', 'Time Out');
        $datetime = array('', '', '', '', '', '', '', '');
        
        for($a=0;$a<count($presets);$a++){
            for($c=0;$c<count($data);$c++){
                if($presets[$a]==$data[$c]->code)
                {
                    $datetime[$a] = $data[$c]->datetime;
                    if($data[$c]->code=="TI")
                        $last_time_in = $data[$c]->datetime;

                    if($data[$c]->code=="TO")
                        $last_time_out = $data[$c]->datetime;
                }
            }
        }

        //if TI or TO is 0 -> get the next TI else calculate
        if(in_array('', $datetime)){ //*** DEFICIENCY *REVISION ONCE HAS DEFICIENCY DECLARE INCOMPLETE 
            //if($datetime[7]==0){ //No Time Out - REMARKKKKKKKKKKKKKKKKKKKKKKSSSS
                //$next_time_in = AttendanceDao::getNextTimeIn($last_time_out, $emp_id);
            //if($datetime[7]==''&&$datetime[0]!='') //No TimeOut but has Time In
             //   $next_time_in = AttendanceDao::getNextTimeIn($last_time_out, $emp_id);
            echo "<u>Incomplete Record</u><br><br>";
            for($c=0;$c<count($datetime);$c++){
                if($datetime[$c]!="")
                    echo date('M d, Y g:i A', strtotime($datetime[$c]))." - ".$presets1[$c]."<br>"; //." - ".$presets[$c]."<br>"
                else
                    echo "No Record&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - ".$presets1[$c]."<br>"; //." - ".$presets[$c]."<br>"
            }
            echo "<br>Total Hours: Incomplete Record";
            return 0;
            //return "Incomplete";
        }
        else //Complete then calculate total hours then check for violation 2 (15 Minutes Break) 1 (1 Hour Lunchbreak)
        {
            //echo "<br>Calculating......<br>";
            echo "<u>Complete Record</u><br><br>";
            $sum = new DateTime('00:00');
            $total = clone $sum;
            
            for($c=0;$c<count($datetime);$c++){
                $f = new DateTime($datetime[$c]);
                echo date('M d, Y g:i A', strtotime($datetime[$c]))." - ".$presets1[$c]."<br>"; //." - ".$presets[$c]."<br>"
                if($c!=3){ //Don't Include Lunch Breaks
                    if(array_key_exists($c+1,$datetime))
                    {
                        $t = new DateTime($datetime[$c+1]);
                        $diff = date_diff($f, $t);
                        //echo $diff->format('%y Years %m Months %d Days %h Hours %i Minutes %s Seconds')."<br>";
                        $diffx[$c] = $diff;
                        $total->add($diffx[$c]);
                    }
                }
            }
            //echo "<br><br>----------TIME DIFF---------------------<br>";
            echo "<br>Total Hours: ".$total->diff($sum)->format('%h Hr %i Min %s Sec'); //%y Years %m Months %d Days 
            return $total->diff($sum)->format('%h Hours %i Minutes %s Seconds');
            //echo "<br>".date('Y-m-j', strtotime('3 weekdays')); die(); I can use this function
            //echo "<br>Total Hours: ".$total->diff($sum)->format('%y Years %m Months %d Days %h Hours %i Minutes %s Seconds');
            
              Total Amount for break - 30 minutes 
              Total Amount for Lunchbreak - 1 Hour
             
        } 
     
        //Debugging
        /*echo "<br><br>-----FOR DEBUGGING---------<br>";
        for($c=0;$c<count($datetime);$c++){
            echo $datetime[$c]." - ".$presets[$c]."<br>";
        }
    
        die('<br>zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz');
    }*/

    function getNextTimeIn($last_time_out, $emp_id){
        var_dump(DB::table('attendance as a')
                ->select('a.datetime')
                ->leftJoin('attendancetype AS t' , 'a.type_id', '=', 't.id')
                ->where('a.emp_id', '=', $emp_id)
                ->where('a.datetime', '>', $last_time_out)
                ->where('t.code', 'TI')
                ->get()
                ->toArray()); die();
    }

    function getAllEmployeeAttendance($from, $to, $accstat){
        //DB::connection()->enableQueryLog();
        return DB::table('employees AS e')
                ->select('e.first_name', 'e.last_name', 'e.is_active', 'e.pos_title', 'a.datetime', 'a.emp_id', 't.code', 't.description')
                ->leftJoin('attendance AS a' , 'e.id', '=', 'a.emp_id')
                ->leftJoin('attendancetype as t', 'a.type_id', '=', 't.id')
                ->whereRaw($accstat)
                ->whereBetween('datetime', [$from, $to])
                ->orderBy('datetime', 'ASC')
                ->get();
        
        //$queries = DB::getQueryLog(); var_dump($queries); die("End of Query");
                //->toSql();
                //->toArray();
    }

    function getMinimumDTOfEmployeeAttendance($accstat){
        $qry = DB::table('attendance')
                   ->select('datetime')
                   ->whereRaw($accstat)
                   ->min('datetime');

        return (is_null($qry)) ? date('Y-m-d 00:00:00') : $qry;
    }

    function getMaximumDTOfEmployeeAttendance($accstat){
        $qry = DB::table('attendance')
                   ->whereRaw($accstat)
                   ->max('datetime');

        return (is_null($qry)) ? date('Y-m-d 00:00:00') : $qry;
    }
}