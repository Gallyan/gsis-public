@extends('emails.layout')

@section('title')
    {{ __('User has changed an address') }}
@endsection

@section('content')
    {{ __('Dear :name', [ 'name' => $name ]) }},<br /><br />

    {{ __('mail-new-address', [ 'user' => $user->name ]) }}.<br /><br />

    @include('emails.button', [
        'link' => route( 'edit-user', $user->id ),
        'text' => __('Show user profile') ])

    <br />

    {!! __('mail-ending') !!}
@endsection