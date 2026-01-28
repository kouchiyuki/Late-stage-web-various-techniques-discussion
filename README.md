# Late-stage-web-various-techniques-discussion
web各術議論の後期提出物

## 掲示板アプリケーション 構築手順
本手順は、Amazon EC2インスタンス（Amazon Linux）上でサービスを構築することを想定しています。

1. Dockerのインストールと自動起動設定
以下のコマンドを実行して、Dockerをインストールし、システム起動時に自動で立ち上がるように設定します。

### Dockerをインストール
sudo yum install -y docker

### Dockerサービスを起動
sudo systemctl start docker

### システム起動時にDockerが自動で起動するように設定
sudo systemctl enable docker

### Dockerコマンドを`ec2-user`でsudoなしで実行できるようにする
sudo usermod -a -G docker ec2-user

### Docker Composeをインストールするディレクトリを作成
sudo mkdir -p /usr/local/lib/docker/cli-plugins/

### Docker Composeのバイナリファイルをダウンロード
sudo curl -SL [https://github.com/docker/compose/releases/download/v2.36.0/docker-compose-linux-x86_64](https://github.com/docker/compose/releases/download/v2.36.0/docker-compose-linux-x86_64) -o /usr/local/lib/docker/cli-plugins/docker-compose

### 実行権限を付与
sudo chmod +x /usr/local/lib/docker/cli-plugins/docker-compose

### インストール確認
docker compose version

### GitHubからプロジェクトをクローン
git clone https://github.com/kouchiyuki/Late-stage-web-various-techniques-discussion.git

### プロジェクトのディレクトリに移動
cd /Late-stage-web-various-techniques-discussion/public

### コンテナのビルドと起動
docker-compose up -d --build

### MySQLコンテナに接続し、テーブルを作成
docker compose exec mysql mysql -u root -pexample_password example_db

会員テーブル
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


2. フォロー関係テーブル
CREATE TABLE IF NOT EXISTS user_relationships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    followee_user_id INT NOT NULL,
    follower_user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

3. 掲示板投稿テーブル
CREATE TABLE IF NOT EXISTS bbs_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    body TEXT,
    image_filename VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

#すべてのセットアップが完了したら、ブラウザでhttp://<EC2インスタンスのパブリックIP>にアクセスして、掲示板の画面が表示されることを確認してください。


#ファイル構成
├── dockertest/
│   ├── public/
│   │   ├── timeline.php      <-- 今回のメイン
│   │   ├── users.php         <-- 会員一覧・検索
│   │   ├── follow_remove.php <-- フォロー解除
│   │   ├── login2.php        <-- ログイン画面
│   │   └── setting/          <-- プロフィール設定フォルダ
│   ├── nginx/
│   │   └── conf.d/
│   │        └── default.conf
│   ├── Dockerfile
│   ├── php.ini
│   └── compose.yml
└── README.md
