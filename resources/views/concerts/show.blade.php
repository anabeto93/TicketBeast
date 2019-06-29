{{--<h2>{{ $concert->title }}</h2>--}}
{{--<h2>{{ $concert->subtitle }}</h2>--}}
{{--<h2>{{ $concert->formatted_date }}</h2>--}}
{{--<p>Doors at {{ $concert->formatted_start_time }}</p>--}}
{{--<p>{{ $concert->ticket_price_in_float }}</p>--}}
{{--<p>{{ $concert->venue }}</p>--}}
{{--<p>{{ $concert->venue_address }}</p>--}}
{{--<p>--}}
    {{--{{ $concert->city }}, {{ $concert->state }} {{$concert->zip}}--}}
{{--</p>--}}
{{--<p>--}}
    {{--{{ $concert->additional_information }}--}}
{{--</p>--}}

@extends('layouts.master')

@section('body')
    <div class="bg-soft p-xs-y-7 full-height">
        <div class="container">
            {{--@if ($concert->hasPoster())--}}
                {{--@include('concerts.partials.card-with-poster', ['concert' => $concert])--}}
            {{--@else--}}
                {{--@include('concerts.partials.card-no-poster', ['concert' => $concert])--}}
            {{--@endif--}}
            @include('concerts.partials.card-no-poster', ['concert' => $concert])
            <div class="text-center text-dark-soft wt-medium">
                <p>Powered by <a href="https://richardopoku.com">Humvite Tech Solutions</a> </p>
            </div>
        </div>
    </div>
@endsection

@push('beforeScripts')
    <script src="https://checkout.stripe.com/checkout.js"></script>
@endpush