<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $param = [
            'user_id' => User::inRandomOrder()->first()->id,
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '名前なら既にあるにゃん！',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=1',
        ];
        DB::table('books')->insert($param);

        $param = [
            'user_id' => User::inRandomOrder()->first()->id,
            'title' => '人を動かす',
            'author' => 'D・カーネギー',
            'isbn' => '9784422100524',
            'published_date' => '1936-10-01',
            'description' => '言うとおりにしろと命じるだけ',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=2',
        ];
        DB::table('books')->insert($param);

        $param = [
            'user_id' => User::inRandomOrder()->first()->id,
            'title' => 'リーダブルコード',
            'author' => 'Dustin Boswell',
            'isbn' => '9784873115658',
            'published_date' => '2012-06-23',
            'description' => '早くて見やすい軽いコード(人それぞれ感性による)',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=3',
        ];
        DB::table('books')->insert($param);

        $param = [
            'user_id' => User::inRandomOrder()->first()->id,
            'title' => '7つの習慣',
            'author' => 'スティーブン・R・コヴィー',
            'isbn' => '9784863940246',
            'published_date' => '2013-08-30',
            'description' => 'まぁ、そりゃそうって感じ',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=4',
        ];
        DB::table('books')->insert($param);

        $param = [
            'user_id' => User::inRandomOrder()->first()->id,
            'title' => '坊っちゃん',
            'author' => '夏目漱石',
            'isbn' => '9784101010021',
            'published_date' => '1906-04-01',
            'description' => '口より先に手が出るタイプ',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=5',
        ];
        DB::table('books')->insert($param);

        $param = [
            'user_id' => User::inRandomOrder()->first()->id,
            'title' => 'サピエンス全史',
            'author' => ' ユヴァル・ノア・ハラリ',
            'isbn' => '9784309226712',
            'published_date' => '2016-09-08',
            'description' => '文明って素晴らしい！',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=6',
        ];
        DB::table('books')->insert($param);

        $param = [
            'user_id' => User::inRandomOrder()->first()->id,
            'title' => 'Clean Code',
            'author' => 'Robert C. Martin',
            'isbn' => '9784048930598',
            'published_date' => '2017-12-18',
            'description' => '見やすいコードを書こう！',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=7',
        ];
        DB::table('books')->insert($param);

        $param = [
            'user_id' => User::inRandomOrder()->first()->id,
            'title' => '嫌われる勇気 ',
            'author' => '岸見一郎・古賀史健',
            'isbn' => '9784478025819',
            'published_date' => '2013-12-13',
            'description' => '他人の価値観を変えるのではなぬ自分を変えろ',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=8',
        ];
        DB::table('books')->insert($param);

        $param = [
            'user_id' => User::inRandomOrder()->first()->id,
            'title' => '火花',
            'author' => '又吉直樹',
            'isbn' => '9784163902302',
            'published_date' => '2015-03-11 ',
            'description' => 'お笑い？と人生？',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=9',
        ];
        DB::table('books')->insert($param);

        $param = [
            'user_id' => User::inRandomOrder()->first()->id,
            'title' => 'FACTFULNESS',
            'author' => 'ハンス・ロスリング',
            'isbn' => '9784822289607',
            'published_date' => '2019-01-11',
            'description' => '世界は別に悪くなっているわけではないらしい',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=10',
        ];
        DB::table('books')->insert($param);

        $param = [
            'user_id' => User::inRandomOrder()->first()->id,
            'title' => 'コンテナ物語',
            'author' => 'マルク・レビンソン',
            'isbn' => '9784822251468',
            'published_date' => '2007-01-18',
            'description' => '流通における「箱」最高！',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=11',
        ];
        DB::table('books')->insert($param);
    }
}
