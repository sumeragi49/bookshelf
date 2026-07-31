# 環境構築
## Laravel環境構築<br>
### Laravelプロジェクト作成(Laravel 10.x)<br>
・Laravel 10.xインストール<br>
 docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html -e COMPOSER_CACHE_DIR=/tmp/composer_cache laravelsail/php82-composer:latest composer create-project laravel/laravel:^10.0 bookshelf-app<br>
 ### Laravel Sailインストール<br>
・プロジェクトディレクトリに移動<br>
    cd bookshelf-app<br>
・Laravel Sailをインストール<br>
    docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html -e COMPOSER_CACHE_DIR=/tmp/composer_cache laravelsail/php82-composer:latest composer require laravel/sail --dev<br>
・Sailの設定ファイルをパブリッシュ（MySQLを選択）<br>
    docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html -e COMPOSER_CACHE_DIR=/tmp/composer_cache laravelsail/php82-composer:latest php artisan sail:install --with=mysql<br>
※M1/M2/M3 Mac（Apple Silicon）をお使いの方：<br>
    sail up -d 実行時に no matching manifest for linux/arm64/v8 エラーが発生した場合、compose.yaml の mysql サービスに platform: 'linux/amd64' を追加してください。<br>
### .envファイル設定<br>
.envは以下のように設定<br>
    DB_CONNECTION=mysql<br>
    DB_HOST=mysql<br>
    DB_PORT=3306<br>
    DB_DATABASE=laravel<br>
    DB_USERNAME=sail<br>
    DB_PASSWORD=password<br>
### フロントエンドのセットアップ(Vite & Tailwind CSS)<br>
1,NPM依存パッケージのインストール<br>
    sail npm install<br>
    ＊sailコンテナ起動、起動していなければ./vendor/bin/sail up -d実行<br>
2,Alpine.jsのインストール<br>
    sail npm install alpinejs<br>
3,Tailwind CSSと@tailwindcss/formsプラグインのインストール<br>
    sail npm install -D tailwindcss@^3.4.0 @tailwindcss/forms postcss autoprefixer<br>
4,設定ファイルの生成<br>
    sail npx tailwindcss init -p<br>
5,Tailwind CSSのテンプレートパス設定とforms プラグインの有効化<br>
    プロジェクト内のtailwind.config.jsファイルを以下に上書き<br>
import defaultTheme from 'tailwindcss/defaultTheme';<br>
import forms from '@tailwindcss/forms';<br>

/** @type {import('tailwindcss').Config} */<br>
export default {<br>
    content: [<br>
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',<br>
        './storage/framework/views/*.php',<br>
        './resources/views/**/*.blade.php',<br>
    ],<br>
    theme: {<br>
        extend: {<br>
            fontFamily: {<br>
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],<br>
            },<br>
        },<br>
    },<br>
    plugins: [forms],<br>
};<br>
6,Vite開発サーバーの起動<br>
    sail npm run dev<br>
### phpMyAdminの追加<br>
・composer.yamlファイルのmysqlの後に以下を記載<br>
phpmyadmin:<br>
    image: 'phpmyadmin:latest'<br>
    ports:<br>
        - '${FORWARD_PHPMYADMIN_PORT:-8080}:80'<br>
    environment:<br>
        PMA_HOST: mysql<br>
        PMA_USER: '${DB_USERNAME}'<br>
        PMA_PASSWORD: '${DB_PASSWORD}'<br>
    networks:<br>
        - sail<br>
    depends_on:<br>
        - mysql<br>
### Sailの起動とエイリアス設定<br>
・sailをバックグラウンドで起動<br>
    ./vendor/bin/sail up -d<br>
・エイリアス設定(./vendor/bin/を省略し、sailのみでコマンドを起動できるようにする)<br>
echo "alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'" >> ~/.zshrc<br>
source ~/.zshrc<br>
### アプリケーションキーの生成<br>
sail artisan key:generate<br>
### データベースのマイグレーションと初期データ投入<br>
・初期データ投入時<br>
sail artisan migrate --seed<br>
・既存のDBをリセット時
sail artisan migrate:fresh --seed<br>

## 開発環境<br>
### 画面遷移<br>
・会員登録ページ http://localhost/register<br>
・ログイン画面 http://localhost/login<br>
・書籍一覧画面(guest) http://localhost<br>
・書籍一覧画面(user) http://localhost/books<br>
・書籍詳細画面 http://localhost/books/{book}<br>
・書籍登録画面 http://localhost/books/create<br>
・書籍編集画面 http://localhost/books{book}/edit<br>
・review編集画面 http://localhost/reviews/{review}/edit<br>
・お気に入り一覧画面 http://localhost/favorites<br>
・ジャンル一覧画面 http://localhost/genres<br>
・ジャンル詳細画面 http://localhost/{genre}<br>
・ジャンル登録画面 http://localhost/create<br>
・ジャンル編集画面 http://localhost/{genre}/edit<br>
・ランキング画面 http://localhost/ranking<br>
・マイ読書レポート画面 http://localhost/reports<br>
・読書計画一覧画面 http://localhost/reading-plans<br>
・読書計画作成画面 http://localhost/reading-plans/create<br>
・読書計画編集画面 http://localhost/reading-plan/{plan}/edit<br>
・通知一覧画面 http://localhost/notifications<br>

### Api<br>
・書籍一覧 http://localhost/api/v1/books<br>
・書籍詳細 http://localhsot/api/v1/books/{book}<br>

## 使用技術(実行環境)<br>

・Laravel Framework "^10.10"<br>
・laravel/fortify: ^1.36"<br>
・laravel/sanctum: ^3.3"<br>
・laravel/tinker: ^2.8"<br>
・laravel/sail: ^1.62"<br>
・phpunit/phpunit: ^10.1"<br>
*laravel-langはアンインストールしました。

## 連携外部サイト<br>

・Google Books API<br>

## PHPUnitに関して<br>
### 事前準備<br>
・compose.yaml<br>
 'services->laravel.test->environment'の項目に<br>
 「PHP_EXTENSION_PCOV: 'true'」を追加する。<br>
・phpunit.xml<br>
 <php>以下に<br>
 <env name="DB_CONNECTION" value="sqlite"/><br>
 <env name="DB_DATABASE" value=":memory:"/><br>
 を追加する。<br>

### testコマンド<br>
 sail artisan test --coverage<br>

## テーブル仕様書<br>

### users テーブル<br>
| カラム名 | 型 | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY |
| --- | --- | --- | --- | --- | --- |
| id | bigint unsigned |  ○  |     |  ○  |     |
| name | vanchar(255) |     |     |  ○  |     |
| email | vanchar(255) |     |     |  ○  |     |
| email_verified_at | timestamp |     |     |     |     |
| password | vanchar(255) |     |     |  ○  |     |
| remember_token |     |     |     |     |     |
| created_at | timestamp |     |     |     |     |
| updated_at | timestamp |     |     |     |     |

### books テーブル<br>
| カラム名 | 型 | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY |
| --- | --- | --- | --- | --- | --- |
| id | bigint unsigned |  ○  |     |  ○  |     |
| user_id | bigint unsigned  |     |     |  ○  |  ○  |
| title | vanchar(255) |     |     |  ○  |     |
| author | vanchar(255) |     |     |  ○  |     |
| isbn | vanchar(255) |     |     |  ○   |     |
| published_date | datetime |     |     |  ○  |     |
| description | vanchar(255) |     |     |     |     |
| image_url | vanchar(255) |     |     |     |     |
| created_at | timestamp |     |     |     |     |
| updated_at | timestamp |     |     |     |     |

### genres テーブル<br>
| カラム名 | 型 | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY |
| --- | --- | --- | --- | --- | --- |
| id | bigint unsigned |  ○  |     |  ○  |     |
| name | vanchar(255)  |     |     |  ○  |    |
| created_at | timestamp |     |     |     |     |
| updated_at | timestamp |     |     |     |     |

### book_genre テーブル<br>
| カラム名 | 型 | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY |
| --- | --- | --- | --- | --- | --- |
| id | bigint unsigned |  ○  |     |  ○  |     |
| book_id | bigint unsigned  |     |     |  ○  |  ○  |
| genre_id | bigint unsigned  |     |     |  ○  |  ○  |
| created_at | timestamp |     |     |     |     |
| updated_at | timestamp |     |     |     |     |

### reviews テーブル<br>
| カラム名 | 型 | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY |
| --- | --- | --- | --- | --- | --- |
| id | bigint unsigned |  ○  |     |  ○  |     |
| user_id | bigint unsigned  |     |     |  ○  |  ○  |
| book_id | bigint unsigned  |     |     |  ○  |  ○  |
| rating | vanchar(255) |     |     |  ○  |     |
| comment | vanchar(255) |     |     |  ○  |     |
| created_at | timestamp |     |     |     |     |
| updated_at | timestamp |     |     |     |     |

### favorites テーブル<br>
| カラム名 | 型 | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY |
| --- | --- | --- | --- | --- | --- |
| id | bigint unsigned |  ○  |     |  ○  |     |
| user_id | bigint unsigned  |     |     |  ○  |  ○  |
| book_id | bigint unsigned  |     |     |  ○  |  ○  |
| created_at | timestamp |     |     |     |     |
| updated_at | timestamp |     |     |     |     |

### likes テーブル<br>
| カラム名 | 型 | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY |
| --- | --- | --- | --- | --- | --- |
| id | bigint unsigned |  ○  |     |  ○  |     |
| user_id | bigint unsigned  |     |     |  ○  |  ○  |
| review_id | bigint unsigned  |     |     |  ○  |  ○  |
| created_at | timestamp |     |     |     |     |
| updated_at | timestamp |     |     |     |     |

### reading_plan<br>
| カラム名 | 型 | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY |
| --- | --- | --- | --- | --- | --- |
| id | bigint unsigned |  ○  |     |  ○  |     |
| user_id | bigint unsigned  |     |     |  ○  |  ○  |
| book_id | bigint unsigned  |     |     |  ○  |  ○  |
| target_date | datetime |     |     |  ○  |     |
| completed_at | datetime |     |     |     |     |
| status | tinyInteger |     |     |  ○  |     |
| created_at | timestamp |     |     |     |     |
| updated_at | timestamp |     |     |     |     |

### notifications<br>
| カラム名 | 型 | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY |
| --- | --- | --- | --- | --- | --- |
| id | bigint unsigned |  ○  |     |  ○  |     |
| type | vanchar(255) |  ○  |     |  ○  |     |
| notifiable_type | vanchar(255) |     |     |  ○  |     |
| notifiable_id | bigint unsigned  |     |     |  ○  |  ○  |
| data | test |     |     |  ○  |     |
| read_at | timestamp |     |     |     |     |
| created_at | timestamp |     |     |     |     |
| updated_at | timestamp |     |     |     |     |

## Seederで作成したデータ<br>
### user<br>
・name: 山田太郎, email: yamada@example.com, password: password<br>
・name: 鈴木花子, email: suzuki@ezample.com, password: password<br>
・name: 田中一郎, email: tanaka@example.com, password: password<br>
・name: 佐藤美咲, email: sato@example.com, password: password<br>
・name: 高橋健太, email: takahashi@example.com, password: password<br>
### genre<br>
「小説」「ビジネス」「技術書」「自己啓発」「エッセイ」「歴史」「科学」「芸術」「料理」「旅行」<br>
### book<br>
| title | author | isbn | published_date | genre | description | image_url |
| --- | --- | --- | --- | --- | --- | --- |
| 吾輩は猫である | 夏目漱石 | 9784101010014 |   1905-01-01  |  小説  |  名前なら既にあるにゃん！  |  https://placehold.co/200x300/e2e8f0/475569?text=1  |
| 人を動かす | D・カーネギー |  9784422100524  |   1936-10-01   |  ビジネス, 自己啓発  |  言うとおりにしろと命じるだけ  |  https://placehold.co/200x300/e2e8f0/475569?text=2  |
| リーダブルコード | Dustin Boswell |   9784873115658   |   2012-06-23   |  技術書  |  早くて見やすい軽いコード(人それぞれ感性による)  |  https://placehold.co/200x300/e2e8f0/475569?text=3  |
|  7つの習慣 | スティーブン・R・コヴィー  |  9784863940246  |  2013-08-30  |  ビジネス, 自己啓発  |  まぁ、そりゃそうって感じ  |  https://placehold.co/200x300/e2e8f0/475569?text=4  |
| 坊っちゃん | 夏目漱石 |  9784101010021  |  1906-04-01  |  小説  |  口より先に手が出るタイプ  |  https://placehold.co/200x300/e2e8f0/475569?text=5  |
| サピエンス全史 | ユヴァル・ノア・ハラリ |  9784309226712  |  2016-09-08  |  歴史, 科学  |  文明って素晴らしい！  |  https://placehold.co/200x300/e2e8f0/475569?text=6  |
| Clean Code | Robert C. Martin |  9784048930598  |  2017-12-18  |  技術書  |  見やすいコードを書こう！  |  https://placehold.co/200x300/e2e8f0/475569?text=7  |
| 嫌われる勇気  | 岸見一郎・古賀史健 |  9784478025819  |  2013-12-13  |  自己啓発  |  他人の価値観を変えるのではなく自分を変えろ  |  https://placehold.co/200x300/e2e8f0/475569?text=8  |
| 火花 | 又吉直樹  |　 9784163902302 　|  2015-03-11  |  小説  |  お笑い？と人生？  |  https://placehold.co/200x300/e2e8f0/475569?text=9  |
| FACTFULNESS | ハンス・ロスリング | 　9784822289607　 |  2019-01-11  |  ビジネス, 科学  |  世界は別に悪くなっているわけではないらしい  |  https://placehold.co/200x300/e2e8f0/475569?text=10  |
| コンテナ物語 | マルク・レビンソン |  9784822251468  |  2007-01-18  |  ビジネス, 歴史  |  流通における「箱」最高！  |  https://placehold.co/200x300/e2e8f0/475569?text=11  |
### review<br>
・32件
・各書籍に5人のユーザーが2～4件のレビュー<br>
・評価は1～5<br>
・ランダムに作成<br>
###　like<br>
・各レビューに対して0～3人のユーザーがいいねするようにランダムに作成<br>
### readingPlan<br>
user_id:1, book_id:1, target_date:2026-08-04, status:0<br>
user_id:1, book_id:2, target_date:2026-08-05, status:0<br>
user_id:1, book_id:3, target_date:2026-08-06, status:0<br>
user_id:1, book_id:4, target_date:2026-08-07, completed_at:2026-07-01, status:2<br>
user_id:1, book_id:5, target_date:2026-08-06, status:0<br>
user_id:2, book_id:1, target_date:2026-08-06, status:0<br>
user_id:3, book_id:2, target_date:2026-08-06, status:0<br>
### notification<br>
type: App\Notifications\InformationNotification<br>
notifiable_type: App\Models\User<br>
notifiable_id: 1(ReadingPlan:1)<br>
data: {"reading_plan_id":1,"body":"「吾輩は猫である」に関するお知らせ","title":"吾輩は猫である"}<br>
created_at: now()<br>

##　Apiの使用(post,update,delete)(c-urlを使用)<br>
### bearer tokenの作成コマンド<br>
  sail tinker(tinkerを開く)<br>
  $user = App\Models\User::first()??App\Models\User::factory()->create();\<br>
  $token = $user->createToken('test-token')->plainTextToken;\<br>
  echo $token;<br>
### postメソッド
 curl -X POST http://127.0.0.1/api/books -H "Authorization: Bearer [作成したトークン]" -H "Accept: application/json" -H "Content-Type: application/json" -d '{"title":"罪と罰", "author":"北垣信之(訳)", "isbn":"9784061330122", "published_date": "1871-01-01", "description": "因果応報,報いを！", "image_url": "https://placehold.co", "genres":[1,6]}'<br>
### updateメソッド
 curl -X PUT http://127.0.0.1/api/books/{book} -H "Authorization: Bearer [作成したトークン]" -H "Accept: application/json" -H "Content-Type: application/json" -d '{"title":"罪と罰(改訂版)", "author":"北垣信之(訳)", "isbn":"9784061330122", "published_date": "1871-01-01", "description": "因果応報,報いを！(改)", "image_url": "https://placehold.co", "genres":[1,6]}'<br>
### deleteメソッド(seederで11個bookを作成しているため,postで作成したデータはid:12)<br>
 curl -X DELETE http://127.0.0.1/api/books/12 -H "Authorization: Bearer [作成したトークン]" -H "Accept: application/json" -H "ContentType: application/json"<br>

## 通知機能(時間設定で通知が鳴るようにしている)の強制コマンド<br>
### 通知送信機能(00:00)<br>
・sail artisan app:send-notifications<br>
### 通知削除機能(01:00)<br>
・sail artisan app:prune-notifications<br>

## ER図<br>
<img width="1450" height="1460" alt="スクリーンショット 2026-07-30 083650" src="https://github.com/user-attachments/assets/24be4592-5270-460c-8b58-0a2909d8298b" />
