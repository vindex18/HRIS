<?php

$app->post('/employee/addemployee', 'EmployeeController:addEmployee'); //Add
$app->get('/employee/employeerecord/{str}', 'EmployeeController:getEmployee'); //Get Single Emp Rec //{emp_id:[0-9]+}
$app->delete('/employee/employeerecord/{str}', 'EmployeeController:deleteEmployee'); //Delete Single Emp Rec
$app->put('/employee/updateemployee/{str}', 'EmployeeController:updateEmployee'); //Update Single Emp Rec
$app->get('/employee[/{accstat:[0-9]+}]', 'EmployeeController:getAllEmployee'); //Get All Emp Rec 