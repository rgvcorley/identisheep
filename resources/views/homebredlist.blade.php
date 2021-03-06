<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 24/11/2016
 * Time: 19:53
 */

?>
@extends('app')
@section('title')
    <title>{!! $title !!}</title>
@stop
@section('content')
    <div style="width:60%;margin-left:20%;">
        <h4>{{$title}}</h4>
        <table class="table table-striped table-bordered table-sm table-condensed print narrow" >
            <thead>
            <tr>
                <th>Date of Application</th>
                <th>Number Deployed</th>
            </tr>
            </thead>
            <tbody>
            @foreach($tags as $tag)
                <tr>
                    <td>{{date('d - m - Y',strtotime($tag->date_applied))}}</td>
                    <td>{{$tag->count}}</td>
                </tr>
            @endforeach
            </tbody>
    </div>
@stop