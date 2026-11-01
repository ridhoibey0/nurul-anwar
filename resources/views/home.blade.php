@extends('layouts.app')

@section('kepala')
    @include('partials.data-terstruktur')
@endsection

@section('content')
    @include('sections.hero')
    @include('sections.statistik')
    @include('sections.bermain')
    @include('sections.jenjang')
    @include('sections.keunggulan')
    @include('sections.keseharian')
    @include('sections.guru')
    @include('sections.testimoni')
    @include('sections.ppdb')
    @include('sections.artikel')
    @include('sections.lokasi')
@endsection
