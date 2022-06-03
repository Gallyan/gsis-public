@extends('emails.layout')

@section('title')
    {{ __('Non-mission purchase').' '.$purchase->id.' '.__($status) }}
@endsection

@section('content')
    {{ __('Dear :name', [ 'name' => $name ]) }},<br /><br />

    {{ __('mail-purchase-'.$status, [
        'id' => $purchase->id,
        'subject' => $purchase->subject,
        'manager' => $manager ]) }}.<br /><br />

    @include('emails.button', [
        'link' => route('edit-purchase', $purchase),
        'text' => __('Show my non-mission purchase') ])

    <br />

    {!! __('mail-ending') !!}
@endsection