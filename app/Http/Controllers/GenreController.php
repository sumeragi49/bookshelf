<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Genre;
use App\Http\Requests\GenreRequest;

class GenreController extends Controller
{
    public function index()
    {
        $genres = Genre::with('books')
               -> withCount('books')
               -> get();

        return view('genres.index', compact('genres'));
    }

    public function create()
    {
        return view('genres.create');
    }

    public function store(GenreRequest $request)
    {
        $genre = Genre::create([
            'name' => $request->input('name'),
        ]);

        return redirect()->route('genres.index')->with('success', 'ジャンルを登録しました。');
    }

    public function show($genreId)
    {
        $genre = Genre::findOrFail($genreId);

        $books = $genre->books()
              -> paginate(10);

        return view('genres.show', compact('genre','books'));
    }

    public function edit($genreId)
    {
        $genre = Genre::findOrFail($genreId);

        return view('genres.edit', compact('genre'));
    }

    public function update(GenreRequest $request, $genreId)
    {
        $genre = Genre::findOrFail($genreId);

        $genre->update([
            'name' => $request->input('name'),
        ]);

        return redirect()->route('genres.index')->with('success', 'ジャンルを編集しました。');
    }

    public function delete($genreId)
    {
        $genre = Genre::findOrFail($genreId);

        $genre->delete();

        return redirect()->route('genres.index')->with('success', 'ジャンルを削除しました。');
    }
}
