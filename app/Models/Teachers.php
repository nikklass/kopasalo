<?php

namespace advancesalary\Models;

use Illuminate\Database\Eloquent\Model;

class Teachers extends Model
{
    //
    protected $table = 'teachers';

    public function loanhistory(){
        return $this->hasMany('advancesalary\Models\LoanHistory');
    }
}
