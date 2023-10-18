# Me_Cool_Custom_Line_Bot

*English Below*

---

***このパッケージは三つのプログラムで構成されています。***

1. **EMBED-IT**: URLやサイトマップからテキストを取得・Embeddingします。PDFやCSVのテキストも使えます。このプロセスにより、テキストのベクトル表現を保存した "vectors.npy" と、それに対応する参照テキストを保存した "reference.json" が生成されます。
   - [コードを見る](https://gitfront.io/r/user-7947009/YwRSkzRF9MHR/EMBED-IT/)
   - クローン: `git clone https://gitfront.io/r/user-7947009/YwRSkzRF9MHR/EMBED-IT.git`

2. **DeepFaissChat**: Chat Botのレスポンスを生成するバックエンドです。
   1. Demoバージョンでは、手動で`app.py`と同じ階層に`dir_{member_id}`フォルダを作成してください（`member_id`は任意ですが、**Me_Cool_Custom_Line_Bot**で設定するMember IDと一致する必要があります）。
   2. **EMBED-IT**で生成した "vectors.npy" と "reference.json" を上記フォルダに配置。
   3. `/config/member_id` でOpenAIのAPIキー等の設定（HTTP Basic認証の初期値は admin/password）。
   - [コードを見る](https://gitfront.io/r/user-7947009/BUNDUzo5yGM9/DeepFaissChat/)
   - クローン: `git clone https://gitfront.io/r/user-7947009/BUNDUzo5yGM9/DeepFaissChat.git`

3. **Me_Cool_Custom_Line_Bot**: クライアント側のWPプラグインです。
   1. **DeepFaissChat**で作成した `dir_{member_id}` の `member_id` をMember IDに設定。
   2. APP URLはローカルでテストする場合 `http://localhost:8080/`。
   3. "Backend Config" ボタンから **DeepFaissChat** の `/config/member_id` にアクセス。
   - [コードを見る](https://gitfront.io/r/user-7947009/NDq3mx4UMsBN/Me-Cool-Custom-Line-Bot/)
   - クローン: `git clone https://gitfront.io/r/user-7947009/NDq3mx4UMsBN/Me-Cool-Custom-Line-Bot.git`

---

## 使用方法

- 通常のWordPressプラグインと同じようにzipにして、インストール・有効化して下さい。
- 設定から「MCC Line Bot」をクリックしてプラグインの設定をします。
- **必須の項目は、Member IDとAPP URLです。**

### ローカルでのテスト
MAMPを使用してWPプラグインのクライアントを立ち上げれば、ローカル環境でもテストが可能です。バックエンドである**DeepFaissChat**をローカルでテストする場合はクライアントもローカルである必要があります。

## 注意事項

このリポジトリは、アプリケーションのアイディア、またはコーディングスキルを評価していただく目的で特定のエンティティに共有しております。以下のポイントにご留意いただけますと幸いです。

### 使用について

- このコードは、アプリケーションのアイディア、またはコーディング能力の評価を目的としています。
- 評価以外での使用はご遠慮いただきますようお願い申し上げます。

### 秘密保持について

- このリポジトリを閲覧することで、秘密保持に関する同意をいただいたものとさせていただきます。
- 評価以外の目的で第三者との共有はご遠慮いただきますようお願い申し上げます。

---

***This package consists of three programs.***

1. **EMBED-IT**: Extracts and embeds text from URLs or sitemaps. Text from PDFs and CSVs can also be used. This process generates "vectors.npy," which stores the vector representations of the text, and "reference.json," which stores the corresponding reference text.
   - [View Code](https://gitfront.io/r/user-7947009/YwRSkzRF9MHR/EMBED-IT/)
   - Clone: `git clone https://gitfront.io/r/user-7947009/YwRSkzRF9MHR/EMBED-IT.git`

2. **DeepFaissChat**: Backend for generating Chat Bot responses.
   1. In the demo version, manually create a `dir_{member_id}` folder at the same level as `app.py` (the `member_id` should match the Member ID set in **Me_Cool_Custom_Line_Bot**).
   2. Place the "vectors.npy" and "reference.json" generated by **EMBED-IT** into the folder created above.
   3. Configure OpenAI API keys and other settings at `/config/member_id` (Initial HTTP Basic Authentication is admin/password).
   - [View Code](https://gitfront.io/r/user-7947009/BUNDUzo5yGM9/DeepFaissChat/)
   - Clone: `git clone https://gitfront.io/r/user-7947009/BUNDUzo5yGM9/DeepFaissChat.git`

3. **Me_Cool_Custom_Line_Bot**: Client-side WP Plugin.
   1. Set the `member_id` from the `dir_{member_id}` created in **DeepFaissChat** as the Member ID.
   2. The APP URL is `http://localhost:8080/` when testing locally.
   3. Access **DeepFaissChat**'s `/config/member_id` from the "Backend Config" button.
   - [View Code](https://gitfront.io/r/user-7947009/NDq3mx4UMsBN/Me-Cool-Custom-Line-Bot/)
   - Clone: `git clone https://gitfront.io/r/user-7947009/NDq3mx4UMsBN/Me-Cool-Custom-Line-Bot.git`

---

## How to Use

- Install and activate the plugin by zipping it like any regular WordPress plugin.
- Click on 'MCC Line Bot' from the settings to configure the plugin.
- **The required fields are Member ID and APP URL.**

### Local Testing
You can test in a local environment using MAMP to launch the WP Plugin client. If you are testing **DeepFaissChat** locally, the client must also be local.

## Important Notice

This repository is shared with specific entities for the purpose of evaluating either the application idea or coding skills. Your attention to the following points would be greatly appreciated.

### Usage Guidelines

- This code is intended solely for the evaluation of either the application idea or coding skills.
- Please refrain from using it for any purpose other than evaluation.

### Confidentiality Agreement

- By accessing this repository, you agree to adhere to a confidentiality agreement.
- Please refrain from sharing this code with third parties for purposes other than evaluation.