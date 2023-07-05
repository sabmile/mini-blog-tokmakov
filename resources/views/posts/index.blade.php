@extends('layouts.site')

@section('content')
    <h1 class="mt-2 mb-3">{{__('Все посты')}}</h1>
    <div class="row">
        @foreach($posts as $post)
            <div class="col-6 mb-5">
                <div class="card">
                    <div class="card-header"><h3>{{ $post->title }}</h3></div>
                    <div class="card-body">
                        <img src="{{ $post->thumb ?? asset('img/default.jpg') }}" alt="" class="img-fluid">
                        <p class="mt-3 mb-2">{{ $post->excerpt }}</p>
                    </div>
                    <div class="card-footer">
                        <div class="clearfix">
                            <span class="float-left">
                                {{__('Автор')}}: {{ $post->author }}<br>
                                {{__('Дата')}}: {{ date_format($post->created_at, 'd.m.Y H:i') }}
                            </span>
                            <a href="{{ route('post.show', ['id' => $post->id]) }}" class="btn btn-dark float-right">{{__('Подробнее')}}</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    {{ $posts->links() }}


@endsection
