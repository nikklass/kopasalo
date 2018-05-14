<?php

namespace advancesalary\Http\Controllers;

use Illuminate\Http\Request;
use advancesalary\Repositories\DatabaseInterface;
use Illuminate\Support\Facades\Input;

class GeneralController extends Controller
{
    //

    protected $databaseInterface;

    public function __construct(DatabaseInterface $databaseInterface)
    {
        $this->databaseInterface = $databaseInterface;
    }

    public function whitelist($mobile,$first_name){
        $mobile1 = $this->databaseInterface->validatePhonenumberInfoBip($mobile,'KE');
        if ($mobile1) {
            $employee = $this->databaseInterface->getEmployeeByMobile($mobile1);
            if ($employee){
                $response['status'] = 'ERROR';
                $response['mobile'] = $mobile1;
                $response['ERROR_MSG'] = 'Mobile number already in use';
                return json_encode($response);
            }else{
                $teacher = $this->databaseInterface->whiteList($mobile1,$first_name);
                if ($teacher) {
                    $response['status'] = 'SUCCESS';
                    $response['mobile'] = $teacher->mobile;
                    $response['first_name'] = $teacher->first_name;
                    $response['payroll_number'] = $teacher->payrol_no;
                    $response['ERROR_MSG'] = 'Employee successful saved';
                    return json_encode($response);
                }else{
                    $response['status'] = 'ERROR';
                    $response['mobile'] = $mobile;
                    $response['first_name'] = null;
                    $response['ERROR_MSG'] = 'Error occurred';
                    return json_encode($response);
                }
            }
        }else{
            $response['status'] = 'ERROR';
            $response['mobile'] = $mobile;
            $response['first_name'] = 'Not found';
            $response['ERROR_MSG'] = 'Invalid mobile phone';
            return json_encode($response);
        }
    }

    public function checkTotalLoan($mobile,$payrol){

        $mobile1 = $this->databaseInterface->validatePhonenumberInfoBip($mobile,'KE');
        if ($mobile1) {

            $employee = $this->databaseInterface->getEmployeeByMobileAndPayrolNumber($mobile1,$payrol);
            if ($employee){
                $response['status'] = 'SUCCESS';
                $response['mobile'] = $mobile1;
                $response['first_name'] = $employee->first_name;
                $response['currency'] = 'KES';
                $response['total_loan'] = $employee->total_loan;
                $response['ERROR_MSG'] = 'OK';
                return json_encode($response);
            }else{
                $response['status'] = 'ERROR';
                $response['mobile'] = $mobile;
                $response['first_name'] = 'Not found';
                $response['ERROR_MSG'] = 'Employee mobile number/payrol not found';
                return json_encode($response);
            }
        }else{
            $response['status'] = 'ERROR';
            $response['mobile'] = $mobile;
            $response['first_name'] = 'Not found';
            $response['ERROR_MSG'] = 'Invalid mobile phone';
            return json_encode($response);
        }
    }

    public function checkLoanHistory($mobile,$payrol){
        $mobile1 = $this->databaseInterface->validatePhonenumberInfoBip($mobile,'KE');
        if ($mobile1) {

            $employee = $this->databaseInterface->getEmployeeByMobileAndPayrolNumber($mobile1,$payrol);
            if ($employee){
                if ($employee->loanhistory->count()>0) {
                    $response = array();
                    $responses = array();
                    $all_responses = array();
                    $i = 0;

                    $response['status'] = 'SUCCESS';
                    $response['mobile'] = $mobile1;
                    $response['first_name'] = $employee->first_name;
                    $response['ERROR_MSG'] = 'OK';

                    foreach ($employee->loanhistory as $loan) {

                        $results['transaction_type'] = $loan->transaction_type;
                        $results['transaction_amount'] = $loan->amount;
                        $results['transaction_date'] = $loan->created_at;
                        $results['currency'] = 'KES';
                        $responses[$i] = $results;
                        $i = $i + 1;
                    }

                    $all_responses['employee'] = $response;
                    $all_responses['transactions'] = $responses;

                    return json_encode($all_responses);

                }else{
                    $response['status'] = 'SUCCESS';
                    $response['mobile'] = $mobile1;
                    $response['first_name'] = $employee->first_name;
                    $response['currency'] = 'KES';
                    $response['total_loan'] = $employee->total_loan;
                    $response['loan_history'] = null;
                    $response['ERROR_MSG'] = 'OK';
                    return json_encode($response);
                }

            }else{
                $response['status'] = 'ERROR';
                $response['mobile'] = $mobile;
                $response['first_name'] = 'Not found';
                $response['ERROR_MSG'] = 'Employee payroll number not found';
                return json_encode($response);
            }
        }else{
            $response['status'] = 'ERROR';
            $response['mobile'] = $mobile;
            $response['first_name'] = 'Not found';
            $response['ERROR_MSG'] = 'Invalid mobile phone';
            return json_encode($response);
        }
    }

    public function getName($mobile){
        //$data=Input::json()->all();
        //$mobile = $data['mobile'];
        //$mobile = $request->mobile;
        $mobile1 = $this->databaseInterface->validatePhonenumberInfoBip($mobile,'KE');

        if (!$mobile1) {
            $response['status'] = 'ERROR';
            $response['mobile'] = $mobile;
            $response['first_name'] = 'Not found';
            $response['ERROR_MSG'] = 'Invalid mobile phone';
            return json_encode($response);
        }

        $employee = $this->databaseInterface->getEmployeeByMobile($mobile1);

        if ($employee){
            $response['status'] = 'SUCCESS';
            $response['mobile'] = $mobile1;
            $response['first_name'] = $employee->first_name;
            $response['ERROR_MSG'] = 'OK';
            return json_encode($response);
        }else{
            $response['status'] = 'ERROR';
            $response['mobile'] = $mobile;
            $response['first_name'] = 'Not found';
            $response['ERROR_MSG'] = 'Employee mobile number not found';
            return json_encode($response);
        }
    }

    public function getMaxDebt($mobile,$payrol_number){
        //$data=Input::json()->all();
        date_default_timezone_set('Africa/Nairobi');
        $today = date('d');
        //$mobile = $data['mobile'];
        //$payrol_number = $data['payrol_number'];
        $mobile1 = $this->databaseInterface->validatePhonenumberInfoBip($mobile,'KE');
        
        if (!$mobile1) {
            $response['status'] = 'ERROR';
            $response['mobile'] = $mobile1;
            $response['first_name'] = 'Not found';
            $response['ERROR_MSG'] = 'Invalid mobile phone';
            return json_encode($response);
        }

        $employee = $this->databaseInterface->getEmployeeByMobileAndPayrolNumber($mobile1,$payrol_number);
        if ($employee){
            $percentage = $this->databaseInterface->getFactors($today);

            if ($percentage){
                $amount_available = ($employee->monthly_salary - $employee->monthly_deductions - $employee->total_loan) * ($percentage/100);
                $amount_available = round($amount_available,2);
                //$amount_available = number_format($amount_available);
                $response['status'] = 'SUCCESS';
                $response['mobile'] = $mobile1;
                $response['payrol_number'] = $payrol_number;
                $response['first_name'] = $employee->first_name;
                $response['currency'] = 'KES';
                $response['amount_available'] = $amount_available;
                $response['ERROR_MSG'] = 'OK';
                return json_encode($response);
            }else{
                $response['status'] = 'ERROR';
                $response['mobile'] = $mobile;
                $response['payrol_number'] = $payrol_number;
                $response['currency'] = 'KES';
                $response['amount_available'] = 0;
                $response['ERROR_MSG'] = 'System settings error occurred';
                return json_encode($response);
            }
        }else{
            $response['status'] = 'ERROR';
            $response['mobile'] = $mobile;
            $response['payrol_number'] = $payrol_number;
            $response['ERROR_MSG'] = 'Employee payroll number not found';
            return json_encode($response);
        }
    }

    public function processTransaction(Request $request){
        $data=Input::json()->all();
        
        $status = $data['status'];
        $payroll_number = $data['payroll_number'];
        $loan_amount = $data['loan_amount'];
        $mobile = $data['mobile'];
        $mobile1 = $this->databaseInterface->validatePhonenumberInfoBip($mobile,'KE');
        if ($mobile1){
            $employee = $this->databaseInterface->getEmployeeByMobileAndPayrolNumber($mobile1,$payroll_number);
            if ($employee){
                $store = $this->databaseInterface->storeHistory('Loan',$loan_amount,$employee->id);
                if ($store){
                    $response['status'] = 'SUCCESS';
                    $response['mobile'] = $mobile;
                    $response['payroll_number'] = $payroll_number;
                    $response['ERROR_MSG'] = 'Transaction stored';
                    return json_encode($response);
                }else{
                    $response['status'] = 'ERROR';
                    $response['mobile'] = $mobile;
                    $response['payroll_number'] = $payroll_number;
                    $response['ERROR_MSG'] = 'This transaction is not stored';
                    return json_encode($response);
                }
            }else{
                $response['status'] = 'ERROR';
                $response['mobile'] = $mobile;
                $response['payroll_number'] = $payroll_number;
                $response['ERROR_MSG'] = 'Employee payroll number not found';
                return json_encode($response);
            }
        }else{
            $response['status'] = 'ERROR';
            $response['mobile'] = $mobile;
            $response['ERROR_MSG'] = 'Employee mobile number is invalid';
            return $response;
        }
    }

    public function processRepayment(){

        $data=Input::json()->all();
        
        $status = $data['status'];
        $payroll_number = $data['payroll_number'];
        $loan_amount = $data['repay_amount'];
        $mobile = $data['mobile'];
        $mobile1 = $this->databaseInterface->validatePhonenumberInfoBip($mobile,'KE');
        if ($mobile1){
            $employee = $this->databaseInterface->getEmployeeByMobileAndPayrolNumber($mobile1,$payroll_number);
            if ($employee){
                $store = $this->databaseInterface->storeHistory('Repayment',$loan_amount,$employee->id);
                if ($store){
                    $response['status'] = 'SUCCESS';
                    $response['mobile'] = $mobile;
                    $response['currency'] = 'KES';
                    $response['loan_balance'] = $store->total_loan;
                    $response['payroll_number'] = $payroll_number;
                    $response['ERROR_MSG'] = 'Transaction stored';
                    return json_encode($response);
                }else{
                    $response['status'] = 'ERROR';
                    $response['mobile'] = $mobile;
                    $response['payroll_number'] = $payroll_number;
                    $response['ERROR_MSG'] = 'This transaction is not stored';
                    return json_encode($response);
                }
            }else{
                $response['status'] = 'ERROR';
                $response['mobile'] = $mobile;
                $response['payroll_number'] = $payroll_number;
                $response['ERROR_MSG'] = 'Employee payroll number not found';
                return json_encode($response);
            }
        }else{
            $response['status'] = 'ERROR';
            $response['mobile'] = $mobile;
            $response['ERROR_MSG'] = 'Employee mobile number is invalid';
        }
    }

}
