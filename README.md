---

## 掲示板アプリケーション 構築手順
※Wed1-2_finaltaskは削除できなかっただけなので、無視してください。

---

## 1. Dockerのインストールと自動起動設定

以下のコマンドを実行して、Dockerをインストールし、システム起動時に自動で立ち上がるように設定します。

### 1.1 Dockerをインストール

```bash
sudo yum install -y docker
````

### 1.2 システム起動時にDockerが自動で起動するように設定

```bash
sudo systemctl start docker
sudo systemctl enable docker
```

### 1.3 Dockerコマンドを `ec2-user` で sudo なしで実行できるようにする

```bash
sudo usermod -a -G docker ec2-user
```

※ 設定反映のため、ログアウトしてください。

---

## 2. Docker Compose のインストール

### 2.1 Docker Composeをインストールするディレクトリを作成

```bash
sudo mkdir -p /usr/local/lib/docker/cli-plugins/
```

### 2.2 Docker Composeのバイナリファイルをダウンロード

```bash
sudo curl -SL https://github.com/docker/compose/releases/download/v2.36.0/docker-compose-linux-x86_64 \
-o /usr/local/lib/docker/cli-plugins/docker-compose
```

### 2.3 実行権限を付与

```bash
sudo chmod +x /usr/local/lib/docker/cli-plugins/docker-compose
```

### 2.4 インストール確認

```bash
docker compose version
```

---

## 3. プロジェクトのセットアップ

#### ※Git がインストールされていない場合

```bash
sudo yum install -y git
git --version
```

### 3.1 GitHubからプロジェクトをクローン

```bash
git clone https://github.com/kouchiyuki/Late-stage-web-various-techniques-discussion.git
```

### 3.2 プロジェクトのディレクトリに移動

```bash
cd Late-stage-web-various-techniques-discussion/public
```

### 3.3 コンテナのビルドと起動

```bash
docker-compose up -d --build
```

---

## 4. データベースの初期設定

### 4.1 MySQLコンテナに接続

```bash
docker compose exec mysql mysql example_db
```

### 4.2 会員テーブル（users）

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

### 4.3 フォロー関係テーブル（user_relationships）

```sql
CREATE TABLE `user_relationships` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `followee_user_id` INT UNSIGNED NOT NULL,
    `follower_user_id` INT UNSIGNED NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### 4.4 掲示板投稿テーブル（bbs_entries）

```sql
CREATE TABLE `bbs_entries` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `body` TEXT NOT NULL,
    `image_filename` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);
```
