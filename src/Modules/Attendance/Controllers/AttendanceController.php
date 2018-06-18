<?php

namespace App\Modules\Attendance\Controllers;
use App\Modules\Attendance\Service\AttendanceService;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class AttendanceController {
    function __construct($container){
       
    }

    function addAttendance(Request $req, Response $res){
        return var_dump(AttendanceService::addAttendance($req, $res));
    }

    function getEmployeeAttendance(Request $req, Response $res, $args){
        return var_dump(AttendanceService::getEmployeeAttendance($req, $res));
    }

    function deleteEmployeeAttendance(Request $req, Response $res, $args){
        return var_dump(AttendanceService::deleteEmployeeAttendance($req, $res));
    }

    function getAllEmployeeAttendance(Request $req, Response $res, $args){
        return var_dump(AttendanceService::getAllEmployeeAttendance($req, $res));
    }
}