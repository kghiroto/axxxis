<?php
$timenow = date("Ymd");
?>

@extends('voyager::master')

@section('content')

<html>
  <header>
  <style>
  /* その他Style  */
  .title-stuff {
    margin-top: 60px;
    font-size: 30px;
    text-align: center;
    }

  /* スマートフォン用CSS */
  @media screen and (max-width: 640px) {

    .container-fluid{
      padding-right: 0;
      padding-left: 0;
    }

    .panel-bordered>.panel-body{
      padding: 0;
    }

    .none{
      display:none;
    }

    .fontsize{
      font-size: 15px;
    }

    .transform{
      transform: translateX(-50px) scale(1.0);
    }

    .btn-primary{
      width: 100% !important;
    }
  }
  </style>
  </header>


  <body>
    <div>
        <p class="title-stuff">スタッフ詳細</p>

    <div class="page-content browse container-fluid">
        @include('voyager::alerts')
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                   <table id="dataTable" class="table table-hover dataTable no-footer" role="grid" aria-describedby="dataTable_info">
                        <thead>
                            <tr role="row">
                                <th class="dt-not-orderable sorting_disabled fontsize none" rowspan="1" colspan="1" aria-label="" style="width: 66px;">
                                    <input type="checkbox" class="select_all">
                                </th>
                                <th class="sorting fontsize" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Name: 昇順の並べ替えを有効にする" style="width: 122px;">名前</th>
                                <th class="sorting fontsize" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Start: 昇順の並べ替えを有効にする" style="width: 196px;">出勤時刻</th>
                                <th class="sorting fontsize" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Finish: 昇順の並べ替えを有効にする" style="width: 206px;">退勤時刻</th>
                                <th class="sorting fontsize" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Start_comment: 昇順の並べ替えを有効にする" style="width: 196px;">勤務地</th>
                                <th class="sorting fontsize" tabindex="0" aria-controls="dataTable" rowspan="1" colspan="1" aria-label="Finish_comment: 昇順の並べ替えを有効にする" style="width: 196px;">実績</th>
                                <th></th>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach(array_reverse($result) as $item)
                            <tr role="row" class="odd">
                                <td class="none">
                                    <input type="checkbox" name="row_id" id="checkbox_1" value="1">
                                </td>
                                <td><div class="fontsize">{{$item["name"]}}</div></td>
                                <td><div class="fontsize">{{$item["start"]}}</div></td>
                                <td><div class="fontsize">{{$item["finish"]}}</div></td>
                                <td><div class="fontsize">{{$item["start_comment"]}}</div></td>
                                <td><div class="fontsize">{{$item["finish_comment"]}}</div></td>
                                <form method="POST" action="{{ route('voyager.attendance-lines.editor') }}">
                                  @csrf
                                  <input type="hidden" name="id" value="{{$item["id"]}}" >
                                <td><button type="submit" class="btn btn-primary" >編集</button></td>
                                </form>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </body>
</html>
@stop
