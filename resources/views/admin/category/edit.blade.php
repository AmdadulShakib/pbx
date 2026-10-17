@extends('admin.layouts.app')
@section('title', __('Edit Category'))
@section('page-title', __('Edit Category'))
@section('content')
    <form action="{{ route('admin.category.update', $category->id) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        @include('admin.components.form.sticky_actions', [
            'cancelUrl' => route('admin.category.index'),
            'submitLabel' => __('Update'),
        ])
    </form>
@endsection
