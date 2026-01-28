
# Late-stage-web-various-techniques-discussion

Web各種技術議論の後期提出物です。
※Wed1-2_finaltaskは削除できなかっただけなので、無視してください。

---

## 掲示板アプリケーション 構築手順

本手順は、**Amazon EC2 インスタンス（Amazon Linux）** 上でサービスを構築することを想定しています。

---

## 1. Dockerのインストールと自動起動設定

以下のコマンドを実行して、Dockerをインストールし、システム起動時に自動で立ち上がるように設定します。

### Dockerをインストール

```bash
sudo yum install -y docker
````

### Dockerサービスを起動

```bash
sudo systemctl start docker
```

### システム起動時にDockerが自動で起動するように設定

```bash
sudo systemctl start docker
sudo systemctl enable docker
```

### Dockerコマンドを `ec2-user` で sudo なしで実行できるようにする

```bash
sudo usermod -a -G docker ec2-user
```

※ 設定反映のため、ログアウト・再ログインしてください。

---

## 2. Docker Compose のインストール

### Docker Composeをインストールするディレクトリを作成

```bash
sudo mkdir -p /usr/local/lib/docker/cli-plugins/
```

### Docker Composeのバイナリファイルをダウンロード

```bash
sudo curl -SL https://github.com/docker/compose/releases/download/v2.36.0/docker-compose-linux-x86_64 \
-o /usr/local/lib/docker/cli-plugins/docker-compose
```

### 実行権限を付与

```bash
sudo chmod +x /usr/local/lib/docker/cli-plugins/docker-compose
```

### インストール確認

```bash
docker compose version
```

---

## 3. プロジェクトのセットアップ

### GitHubからプロジェクトをクローン

```bash
git clone https://github.com/kouchiyuki/Late-stage-web-various-techniques-discussion.git
```

### プロジェクトのディレクトリに移動

```bash
cd Late-stage-web-various-techniques-discussion/public
```

### コンテナのビルドと起動

```bash
docker-compose up -d --build
```

---

## 4. データベースの初期設定

### MySQLコンテナに接続

```bash
docker compose exec mysql mysql -u root -pexample_password example_db
```

### 会員テーブル（users）

```sql
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255),
  email VARCHAR(255),
  password VARCHAR(255),
  salt VARCHAR(255),
  icon_filename VARCHAR(255),
  cover_filename VARCHAR(255),
  self_introduction TEXT,
  introduction TEXT,
  birthday DATE,
  birth_year INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### フォロー関係テーブル（user_relationships）

```sql
CREATE TABLE IF NOT EXISTS user_relationships (
  id INT AUTO_INCREMENT PRIMARY KEY,
  followee_user_id INT NOT NULL,
  follower_user_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 掲示板投稿テーブル（bbs_entries）

```sql
CREATE TABLE IF NOT EXISTS bbs_entries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  body TEXT,
  image_filename VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 5. 動作確認

すべてのセットアップが完了したら、以下のURLにアクセスしてください。

```
http://<EC2インスタンスのパブリックIP>
```

掲示板の画面が表示されれば成功です。

---

## ファイル構成

```text
├── dockertest/
│   ├── public/
│   │   ├── timeline.php      # 今回のメイン
│   │   ├── users.php         # 会員一覧・検索
│   │   ├── follow_remove.php # フォロー解除
│   │   ├── login2.php        # ログイン画面
│   │   └── setting/          # プロフィール設定フォルダ
│   │       ├── birthday.php　　　　#生年月日設定
│   │       ├── cover.php          #カバー画像設定
│   │       ├── icon.php           #アイコン設定
│   │       ├── index.php          #設定画面トップ
│   │       └── introduction.php   #自己紹介文設定
│   ├── nginx/
│   │   └── conf.d/
│   │        └── default.conf
│   ├── Dockerfile
│   ├── php.ini
│   └── compose.yml
└── README.md
```
