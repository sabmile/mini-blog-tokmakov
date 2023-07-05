@extends('layouts.site')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mt-4 mb-4">
                <div class="card-header">
                    <h1>{{ $post->title }}</h1>
                </div>
                <div class="card-body">
                    <img src="{{ $post->image ?? asset('img/default.jpg') }}" alt="" class="img-fluid">
                    <p class="mt-3 mb-0">{{ $post->body }}</p>
                </div>
                <div class="card-footer">
                    <div class="clearfix">
                        <span class="float-left">
                            {{__('Автор')}}: {{ $post->author->name }} <br>
                            {{__('Дата')}}: {{ date_format($post->created_at, 'd.m.Y H:i') }}
                        </span>
                        <a href="{{ route('post.edit', ['id' => $post->id]) }}" class="btn btn-dark float-right">Редактировать</a>
                        <form action="{{ route('post.destroy', ['id' => $post->id]) }}"
                            method="post" onsubmit="return confirm('Удалить этот пост?')"
                            class="d-inline">
                          @csrf
                          @method('DELETE')
                          <input type="submit" class="btn btn-danger" value="Удалить">
                      </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
