<?php

$app->get('/attendance/allemployeeattendance/{dtfrom:[0-9]+}/{dtto:[0-9]+}/[{accstat}]', 'AttendanceController:getAllEmployeeAttendance');
$app->get('/attendance/employeeattendance/{dtfrom:[0-9]+}/{dtto:[0-9]+}/{emp_id}', 'AttendanceController:getEmployeeAttendance');
$app->delete('/attendance/employeeattendance/{att_id}', 'AttendanceController:deleteEmployeeAttendance');
$app->post('/attendance/employeeattendance', 'AttendanceController:addAttendance');