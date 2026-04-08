@extends('welcome')

@section('content')
@vite(['resources/css/app.css', 'resources/js/app.js'])

<style>
    .content-wrapper { background: transparent !important; }
</style>

<div class="content-wrapper">
    <section class="content" style="padding-top: 10px;">
        <div class="container-fluid">
            <div id="nrdi-dashboard-root"></div>
        </div>
    </section>
</div>
@endsection
