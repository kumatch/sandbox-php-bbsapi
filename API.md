API
====

リソース | 説明
------- | ----
POST /user/register | ユーザ登録
POST /user/authorize | ユーザ認証 (ログイン)
POST /threads | 新しいスレッドを作成
DELETE /threads/{thread_id} | 特定のスレッドを削除
GET /threads/{thread_id} | 特定のスレッド情報を取得
GET /threads?tags=foo,bar,baz | タグを指定してスレッド情報を一覧取得
POST /threads/{thread_id}/posts | 特定のスレッドへ新しいポストを投稿
GET /threads/{thread_id}/posts | 特定のスレッドのポスト情報を一覧取得
GET /threads/{thread_id}/posts/{post_id} | 特定のスレッドポスト情報を取得

POST /user/register
----------

### Request

以下の内容を JSON 形式で送信。

要素名 | 説明 | 条件
------- | ---- | ----
email | メールアドレス | 1 〜 255 文字, メールアドレス形式
username | ユーザ名 | 1 〜 10 文字, アルファベット, 数字、アンダースコアのみ
password | パスワード | 10 文字以上, ASCII 文字のみ (アルファベット、数字、記号)

```
{
  "email": "foo@example.com",
  "username": "foo",
  "password": "vYZeJj2rLR.rHPW"
}
```

### Response

#### 201 Created

ユーザ登録に成功。登録されたユーザ情報が返される。

要素名 | 説明
------- | ----
email | 登録ユーザのメールアドレス
username | 登録ユーザのユーザ名


```
{
    "email": "foo@example.com",
    "username": "foo"
}
```

##### 400 Bad Request

ユーザ登録のためのリクエストデータが条件に適していない場合。



POST /user/authorize
----

### Request

以下の内容を JSON 形式で送信。

要素名 | 説明 | 条件
------- | ---- | ----
username | ユーザ名 | 登録されているユーザ名
password | パスワード | ユーザ名に対するパスワード

```
{
  "username": "foo",
  "password": "vYZeJj2rLR.rHPW"
}
```

### Response

#### 200 OK

ログインに成功。他の API アクセスに必要なユーザ識別子とアクセストークン、およびその有効期限が返される。

要素名 | 説明
------- | ----
id | 認証ユーザの識別子
token | 認証ユーザのアクセストークン
period | アクセストークンの有効期限 (Unix タイム形式)

```
{
    "id": 1,
    "token": "AJdEp9ZDzy8iraRhtqncnos.t0pOETNSNwXIz0zC",
    "period": 1443181998
}
```

#### 400 Bad Request

ユーザ認証のためのリクエストデータが条件に適していない場合。

#### 401 Unauthorized

ユーザ認証に失敗。




POST /threads
----

スレッドの作成を行うことができるのは登録ユーザのみ。
先にユーザ認証を行って API アクセストークンを作成して、リクエストにアクセストークンを指定する必要がある。

### Request

以下の内容を JSON 形式で送信。

要素名 | 説明 | 条件
------- | ---- | ----
title | スレッドのタイトル | 1 〜 40 文字まで。空白文字のみは NG。
tags | タグ (複数指定可) | 0 以上のタグをリストで指定。1 つのタグは 1 〜 20 文字まで。空白文字のみは NG。

```
{
  "title": "テストスレッド",
  "tags": [ "test", "テスト" ]
}
```

### Response

#### 201 Created

新しいスレッドの作成に成功。作成されたスレッド情報が返される。

要素名 | 説明
------- | ----
id | スレッドの識別子
title | スレッドのタイトル
created_at | 作成日時 (Unix タイム形式)
tags | スレッドに設定されたタグのリスト

```
{
    "id": 42,
    "title": "テストスレッド",
    "created_at": 1443096399,
    "tags": [
        "test",
        "テスト"
    ]
}
```

#### 400 Bad Request

スレッド作成のためのリクエストデータが条件に適していない場合。

#### 401 Unauthorized

API アクセストークンを用いたユーザの特定に失敗。





DELETE /threads/{thread_id}
----

スレッドの削除を行うことができるのは登録ユーザで、かつそのスレッドを作成したユーザのみ。
先にユーザ認証を行って API アクセストークンを作成して、リクエストにアクセストークンを指定する必要がある。

### Request

特になし。

### Response

#### 200 OK

特定スレッドの削除に成功。

#### 401 Unauthorized

API アクセストークンを用いたユーザの特定に失敗。

#### 403 Forbidden

特定スレッドを削除する権利がない。（作成したユーザではない）

#### 404 Not Found

特定スレッドが存在しない。






GET /threads/{thread_id}
----

### Request

特になし。

### Response

#### 200 OK

特定スレッドの参照に成功。スレッド情報が返される。

要素名 | 説明
------- | ----
id | スレッドの識別子
title | スレッドのタイトル
created_at | 作成日時 (Unix タイム形式)
tags | スレッドに設定されたタグのリスト

```
{
    "id": 42,
    "title": "テストスレッド",
    "created_at": 1443096399,
    "tags": [
        "test",
        "テスト"
    ]
}
```

#### 404 Not Found

特定スレッドが存在しない。



GET /threads?tags=foo,bar,baz
----

### Request

クエリストリング `tags` に参照したいスレッドが保持するタグを指定する。
カンマ区切りで複数指定可。


### Response

#### 200 OK

指定したタグに対するスレッドの情報がリストで返される。
リスト内のスレッド情報は以下のとおり。

要素名 | 説明
------- | ----
id | スレッドの識別子
title | スレッドのタイトル
created_at | 作成日時 (Unix タイム形式)
tags | スレッドに設定されたタグのリスト

```
[
    {
        "id": 27,
        "title": "サンプルスレッド",
        "created_at": 1442881802,
        "tags": [
            "test",
            "sample",
        ]
    },
    {
        "id": 42,
        "title": "テストスレッド",
        "created_at": 1443096399,
        "tags": [
            "test",
            "テスト"
        ]
    }
]
```



POST /threads/{thread_id}/posts
----

### Request

以下の内容を JSON 形式で送信。

要素名 | 説明 | 条件
------- | ---- | ----
content | ポスト内容| 1 〜 10000 文字まで。

```
{
  "content": "こんにちは。\nこれはテスト投稿です。",
}
```

### Response

#### 201 Created

新しいスレッドポストの投稿に成功。投稿情報が返される。

要素名 | 説明
------- | ----
id | ポストの識別子
thread_id | スレッドの識別子
content | 投稿内容
created_at | 作成日時 (Unix タイム形式)

```
{
    "id": 127,
    "thread_id": 42,
    "content": "こんにちは。\nこれはテスト投稿です。",
    "created_at": 1443097501
}
```

#### 400 Bad Request

スレッドポストの投稿リクエストデータが条件に適していない場合。

#### 404 Not Found

特定スレッドが存在しない。



GET /threads/{thread_id}/posts
----

### Request

特になし。

### Response

#### 200 OK

特定スレッドに投稿された全てのポスト情報のリストが返される。

要素名 | 説明
------- | ----
id | ポストの識別子
thread_id | スレッドの識別子
content | 投稿内容
created_at | 作成日時 (Unix タイム形式)

```
[
    {
        "id": 83,
        "thread_id": 42,
        "content": "初投稿。",
        "created_at": 1443097007
    },
    {
        "id": 127,
        "thread_id": 42,
        "content": "こんにちは。\nこれはテスト投稿です。",
        "created_at": 1443097501
    }
]
```

#### 404 Not Found

特定スレッドが存在しない。






GET /threads/{thread_id}/posts/{post_id}
----

### Request

特になし。

### Response

#### 200 OK

特定ポストの参照に成功。ポスト情報が返される。

要素名 | 説明
------- | ----
id | ポストの識別子
thread_id | スレッドの識別子
content | 投稿内容
created_at | 作成日時 (Unix タイム形式)

```
{
    "id": 127,
    "thread_id": 42,
    "content": "こんにちは。\nこれはテスト投稿です。",
    "created_at": 1443097501
}
```

#### 404 Not Found

特定ポストが存在しない。
