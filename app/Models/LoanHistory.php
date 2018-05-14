<?php

namespace advancesalary\Models;

use Illuminate\Database\Eloquent\Model;

class LoanHistory extends Model
{
    //
    protected $table = 'loan_histories';

    public function teacher(){
        return $this->belongsTo('advancesalary\Models\Teachers','teachers_id');
    }
}
