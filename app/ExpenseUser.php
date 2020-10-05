<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class ExpenseUser extends Model
{
  // 絶対に変更しないカラム
  protected $guarded = ['id'];
}
