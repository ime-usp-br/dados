@extends('laravel-usp-theme::master')

@section('title')
  @parent 
@endsection

@section('styles')
  @parent
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
@endsection

@section('javascripts_bottom')
  @parent
  <script>
    // Seu código .js
  </script>
@endsection