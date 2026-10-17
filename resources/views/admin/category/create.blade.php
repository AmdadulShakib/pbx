@extends('admin.layouts.app')
@section('title', __('Add New Category'))
@section('page-title', __('Add New Category'))
@section('content')
    <form action="{{ route('admin.category.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        
        @include('admin.components.form.sticky_actions', [
            'cancelUrl' => route('admin.category.index'),
            'submitLabel' => __('Save'),
        ])
    </form>
@endsection
