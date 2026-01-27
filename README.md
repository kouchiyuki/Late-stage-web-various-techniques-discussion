# Late-stage-web-various-techniques-discussion
web各術議論の後期提出物

掲示板アプリケーション 構築手順
本手順は、Amazon EC2インスタンス（Amazon Linux）上でサービスを構築することを想定しています。

1. Dockerのインストールと自動起動設定
以下のコマンドを実行して、Dockerをインストールし、システム起動時に自動で立ち上がるように設定します。

# Dockerをインストール
sudo yum install -y docker

# Dockerサービスを起動
sudo systemctl start docker

# システム起動時にDockerが自動で起動するように設定
sudo systemctl enable docker

# Dockerコマンドを`ec2-user`でsudoなしで実行できるようにする
sudo usermod -a -G docker ec2-user

# Docker Composeをインストールするディレクトリを作成
sudo mkdir -p /usr/local/lib/docker/cli-plugins/

# Docker Composeのバイナリファイルをダウンロード
sudo curl -SL [https://github.com/docker/compose/releases/download/v2.36.0/docker-compose-linux-x86_64](https://github.com/docker/compose/releases/download/v2.36.0/docker-compose-linux-x86_64) -o /usr/local/lib/docker/cli-plugins/docker-compose

# 実行権限を付与
sudo chmod +x /usr/local/lib/docker/cli-plugins/docker-compose

# インストール確認
docker compose version

# GitHubからプロジェクトをクローン
git clone 

# プロジェクトのディレクトリに移動
cd -1-2-/public

# コンテナのビルドと起動
docker-compose up -d --build

# MySQLコンテナに接続し、`bbs_entries`テーブルを作成
※【重要】私が作成したものはmysql2と名前を付けています。！！！
docker-compose exec mysql2 mysql -u root -pexample_password example_db -e "CREATE TABLE IF NOT EXISTS bbs_entries (id INT AUTO_INCREMENT PRIMARY KEY, body TEXT NOT NULL, image_filename VARCHAR(255), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);"

#すべてのセットアップが完了したら、ブラウザでhttp://<EC2インスタンスのパブリックIP>にアクセスして、掲示板の画面が表示されることを確認してください。


#ファイル構成
.
├── -1-2-/
│   ├── public/
│   │   ├── finalassignment2.php
│   │   ├── enshu1_view.php
│   │   ├── bbsimagetest.php
│   ├── nginx/
│   │   └── conf.d
|   |    　　└── default.conf
│   ├── Dockerfile
│   └── compose.yml
└── README.md
