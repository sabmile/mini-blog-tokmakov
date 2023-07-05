<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;


class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(): View
    {
        $posts = Post::select('posts.*', 'users.name as author')
            ->join('users', 'posts.author_id', '=', 'users.id')
            ->orderBy('posts.created_at', 'desc')
            ->paginate(4);
        return view('posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): View
    {
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $post = new Post();

        $post->author_id = rand(1, 4);
        $post->title = $request->input('title');
        $post->excerpt = $request->input('excerpt');
        $post->body = $request->input('body');
        $source = $request->file('image');


        if ($source) {
            $manager = new ImageManager(['driver' => 'imagick']);

            $ext = str_replace('jpeg', 'jpg', $source->extension());
            $name = md5(uniqid());
            Storage::putFileAs('public/image/source', $source, $name. '.' . $ext);
            $image = $manager->make($source)->resizeCanvas(1200, 400, 'center', false, 'dddddd')->encode('jpg', 100);
            Storage::put('public/image/image/' . $name . '.jpg', $image);
            $image->destroy();
            $post->image = Storage::url('public/image/image/' . $name . '.jpg');
            $thumb = $manager->make($source)->resizeCanvas(600, 200, 'center', false, 'dddddd')->encode('jpg', 100);
            Storage::put('public/image/thumb/' . $name . '.jpg', $thumb);
            $thumb->destroy();
            $post->thumb = Storage::url('public/image/thumb/' . $name . '.jpg');
        }

        $post->save();

        return redirect()->route('post.index')->with('success', 'Новый пост успешно создан');


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id): View
    {
        $post = Post::findOrFail($id);
        return view('posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id): View
    {
        $post = Post::find($id);
        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $post = Post::find($id);
        $post->title = $request->input('title');
        $post->excerpt = $request->input('excerpt');
        $post->body = $request->input('body');

        if ($request->input('remove')) {
            $this->removeImage($post);
        }

        $this->uploadImage($request, $post);
        $post->update();

        return redirect()->route('post.show', compact('id'))->with('success', 'Пост успешно отредактирован');
    }

    private function uploadImage(Request $request, Post $post) {
        if ($request->input('remove')) {

            if (!empty($post->image)) {
                $name = basename($post->image);
                if (Storage::exists('public/image/image/' . $name)) {
                    Storage::delete('public/image/image/' . $name);
                }
                $post->image = null;
            }

            if (!empty($post->thumb)) {
                $name = basename($post->thumb);
                if (Storage::exists('public/image/thumb/' . $name)) {
                    Storage::delete('public/image/thumb/' . $name);
                }
                $post->thumb = null;
            }

            if (!empty($name)) {
                $images = Storage::files('public/image/source');
                $base = pathinfo($name, PATHINFO_FILENAME);
                foreach ($images as $img) {
                    $temp = pathinfo($img, PATHINFO_FILENAME);
                    if ($temp == $base) {
                        Storage::delete($img);
                        break;
                    }
                }
            }
        }

        $source = $request->file('image');
        if ($source) {
            $ext = str_replace('jpeg', 'jpg', $source->extension());
            $name = md5(uniqid());
            Storage::putFileAs('public/image/source', $source, $name. '.' . $ext);
            $manager = new ImageManager(['driver' => 'imagick']);
            $image = $manager->make($source)->resizeCanvas(1200, 400, 'center', false, 'dddddd')->encode('jpg', 100);
            Storage::put('public/image/image/' . $name . '.jpg', $image);
            $image->destroy();
            $post->image = Storage::url('public/image/image/' . $name . '.jpg');
            $thumb = $manager->make($source)->resizeCanvas(600, 200, 'center', false, 'dddddd')->encode('jpg', 100);
            Storage::put('public/image/thumb/' . $name . '.jpg', $thumb);
            $thumb->destroy();
            $post->thumb = Storage::url('public/image/thumb/' . $name . '.jpg');
        }
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): RedirectResponse
    {
        $post = Post::findOrFail($id);
        $this->removeImage($post);
        $post->delete();
        return redirect()->route('post.index')->with('success', 'Пост был успешно удален');
    }

    private function removeImage(Post $post) {
        if (!empty($post->image)) {
            $name = basename($post->image);
            if (Storage::exists('public/image/image/' . $name)) {
                Storage::delete('public/image/image/' . $name);
            }
            $post->image = null;
        }
        if (!empty($post->thumb)) {
            $name = basename($post->thumb);
            if (Storage::exists('public/image/thumb/' . $name)) {
                Storage::delete('public/image/thumb/' . $name);
            }
            $post->thumb = null;
        }
        // здесь сложнее, мы не знаем, какое у файла расширение
        if (!empty($name)) {
            $images = Storage::files('public/image/source');
            $base = pathinfo($name, PATHINFO_FILENAME);
            foreach ($images as $img) {
                $temp = pathinfo($img, PATHINFO_FILENAME);
                if ($temp == $base) {
                    Storage::delete($img);
                    break;
                }
            }
        }
    }

    public function search(Request $request): View
    {
        $search = $request->input('search', '');
        $search = iconv_substr($search, 0, 64);
        $search = preg_replace('#[^0-9a-zA-ZА-Яа-яёЁ]#u', ' ', $search);
        $search = preg_replace('#\s+#u', ' ', $search);


        if (empty($search)) {
            return view('posts.search');
        }

        $posts = Post::select('posts.*')
            ->join('users', 'posts.author_id', '=', 'users.id')
            ->where('posts.title', 'like', '%'. $search . '%')
            ->orWhere('posts.body', 'like', '%'. $search . '%')
            ->orWhere('users.name', 'like', '%'. $search . '%')
            ->orderBy('posts.created_at', 'desc')
            ->paginate(4)
            ->appends(['search' => $request->input('search')]);

        return view('posts.search', compact('posts'));
    }
}
