<?php

namespace App\Modules\Employee\Service;

use App\Modules\Employee\Models\EmployeeModel;
use App\Modules\Employee\Dao\EmployeeDao;
use Respect\Validation\Validator as v;
use App\Utils\Validator;

class EmployeeService {
    function __construct($container){

    }

    function checkEmailExistsOnAddEmployee($email){
        return EmployeeModel::where('email', '=', strip_tags($email))->where('is_active', '=', 1)->first();
    }

    function getAllEmployee($req, $res){
        $accstat = strip_tags($req->getAttribute('accstat'));
        (empty($accstat)) ? $accstat = "is_active IS NOT NULL" : $accstat = "is_active = ".$accstat;
        return EmployeeModel::whereRaw($accstat)->orderBy('created_at', 'DESC')->get();
    }

    function deleteemployee($req, $res){
        $emp_id = base64_decode(urldecode($req->getAttribute('str')));
        return (is_numeric($emp_id)) ?EmployeeModel::where('id', $emp_id)->delete() : null;
    }

    function updateEmployee($req, $res){

    }

    function addEmployee($req, $res){
        
        $validation = Validator::validate($req, [
            'firstname' => v::notEmpty()->alpha(),
            'lastname' => v::notEmpty()->alpha(),
            'middlename' => v::notEmpty()->alpha(),
            'password' => v::noWhitespace()->notEmpty()->length(6, null),
            'email' => v::noWhitespace()->notEmpty()->email(),
        ]); 
        
        if(!is_null($validation))
           return $res->withJSON($validation);
        
        $userexists = EmployeeService::checkEmailExistsOnAddEmployee(strip_tags($req->getParam('email')));
        
        if(!is_null($userexists)){
            return array('status' => 'val_error', 'msg' => 'Email Already Exists!', 'tk' => ''); 
        }
        else{
            $qry = EmployeeModel::firstOrcreate([
                                'first_name' => strip_tags($req->getParam('firstname')), 
                                'last_name' => strip_tags($req->getParam('lastname')),
                                'middle_name' => strip_tags($req->getParam('middlename')),
                                'phone' => strip_tags($req->getParam('phone')),
                                'email' => strip_tags($req->getParam('email')),
                                'address' => strip_tags($req->getParam('address')),
                                'pos_title' => strip_tags($req->getParam('postitle')),
                                'password' => password_hash(strip_tags($req->getParam('password')), PASSWORD_DEFAULT)
                                ])->save();

            if($qry)
                return array('status' => 'success', 'msg' => $qry, 'tk' => ''); 
            else
                return array('status' => 'failed', 'msg'=> 'No User Found!', 'tk' =>'');
        }
    }

    function getEmployee($req, $res, $args){
        $emp_id = base64_decode(urldecode($req->getAttribute('str')));
        //$emp_id = strip_tags($req->getAttribute('emp_id')); 
        return (is_numeric($emp_id)) ? EmployeeModel::where('id', $emp_id)->first()->toArray() : null;
        //else
        //var_dump("On Going"); die();
    }
}
