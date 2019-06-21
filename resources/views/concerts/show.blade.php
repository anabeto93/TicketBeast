<h2>{{ $concert->title }}</h2>
<h2>{{ $concert->subtitle }}</h2>
<h2>{{ $concert->formatted_date }}</h2>
<p>Doors at {{ $concert->formatted_start_time }}</p>
<p>{{ $concert->ticket_price_in_float }}</p>
<p>{{ $concert->venue }}</p>
<p>{{ $concert->venue_address }}</p>
<p>
    {{ $concert->city }}, {{ $concert->state }} {{$concert->zip}}
</p>
<p>
    {{ $concert->additional_information }}
</p>