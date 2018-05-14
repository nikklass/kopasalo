<?php
/**
 * Created by IntelliJ IDEA.
 * User: Collins
 * Date: 11/3/2017
 * Time: 10:17 AM
 */

namespace advancesalary\Repositories;
use advancesalary\User;
use advancesalary\Models\Teachers;
use advancesalary\Models\Factors;
use advancesalary\Models\LoanHistory;

class DatabaseInterface
{
    protected $teachers;
    protected $user;
    protected $factors;
    protected $loanHistory;

    public function __construct(User $user,Teachers $teachers,Factors $factors,LoanHistory $loanHistory){
        $this->teachers = $teachers;
        $this->user = $user;
        $this->factors = $factors;
        $this->loanHistory = $loanHistory;
    }

    public function whiteList($mobile, $firstname){
        $ttl = $this->getTotalTeachers() + 1;
        $payrol = '00'.$ttl;
        $teach = $this->teachers;

        $teach->payrol_no = $payrol;
        $teach->first_name = $firstname;
        $teach->mobile = $mobile;
        $teach->monthly_salary = '70000';
        $teach->monthly_deductions = '10000';
        $teach->national_id = $payrol.'00';
        $teach->save();
        return $teach;
    }

    //loan history
    public function storeHistory($type,$amount,$teacher_id){
        $history = $this->loanHistory;
        $history->transaction_type = $type;
        $history->amount = $amount;
        $history->teachers_id = $teacher_id;
        $history->save();
        

        if ($type == 'Loan') {
            $teach = $this->teachers->find($teacher_id);
            $new_loan = $teach->total_loan + $amount;
            $teach->total_loan = $new_loan;
            $teach->save();
        }elseif ($type == 'Repayment') {
            $teach = $this->teachers->find($teacher_id);
            $new_loan = $teach->total_loan - $amount;
            $teach->total_loan = $new_loan;
            $teach->save();
            return $teach;
        }
        return $history;
    }

    //teachers
    public function getEmployeeByMobile($mobile){
        return $this->teachers->all()->where('mobile',$mobile)->first();
    }
    public function getEmployeeByMobileAndPayrolNumber($mobile,$payrol){
        $employee = $this->teachers->all()->where('mobile',$mobile)->where('payrol_no',$payrol)->first();
        if ($employee){
            return $employee;
        }else{
            return false;
        }
    }

    //factor
    public function getFactors($day_of_month){
        $factors = $this->factors->all();
        if ($factors->count() > 0){
            foreach ($factors as $fact){
                if ( ($day_of_month >= $fact->from_) && ($day_of_month <= $fact->to_) ){
                    return $fact->percentage_out;
                }
            }
        }else{
            return false;
        }
    }

    public function validatePhonenumberInfoBip($numbers,$country_code){
        $swissNumberStr = $numbers;
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        try {
            $swissNumberProto = $phoneUtil->parse($swissNumberStr, $country_code);
            // var_dump($swissNumberProto);
            if ($phoneUtil->isValidNumber($swissNumberProto)) {
                # code...
                $phone = $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::E164);
                return str_replace("+","",$phone);
            }else{
                return false;
            }
        } catch (\libphonenumber\NumberParseException $e) {
            return false;
        }

    }

    public function getTotalTeachers(){
        return $this->teachers->all()->count();
    }


}