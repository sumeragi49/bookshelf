<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Book;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        if ($users->isEmpty() || $books->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            $favoriteCount = rand(3, 5);
            $randomBooks = $books->shuffle()->take($favoriteCount)->pluck('id');

            $user->favoriteBooks()->sync($randomBooks);

            //foreach ($randomBooks as $book) {
                //DB::table('favorites')->insert([
                    //'user_id' => $user->id,
                    //'book_id' => $book->id,
                //]);
            //}
        }
    }
}
