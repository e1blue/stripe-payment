# Stripe Payment
WordPress Plugin

[Stripe Payment マニュアル](https://gt1.xyz/stripe-payment-manual/)

2018/12/5    1.6.2<br>
**品切れメッセージ設定追加**
+ 商品が品切れになった際のメッセージ（HTML可）設定を追加。plan_idが存在しなくても定期課金が作れるように修正。

**plan_idが存在しない場合にエラーとなっていた不具合を修正**
+ subscription="on" interval="month"（など） があり plan_idがない場合エラーになっていたのを修正。plan_idを item_年月日 で作成されるようにした。

2018/12/1   1.6.1<br>
+ 送信元メール情報、管理者宛メール情報設定追加

2018/11/29<br>
**パラメータに metadata を追加**
+ metadata="name,company,gender" と指定すると Stripe の受注時メタデータに name, company, gender 値を追加します。

2018/09/15<br>
**同一画面に複数決済ボタンが存在するときに2つ目以降でリクエストが削除されていた不具合を修正**

2018/09/14<br>
**決済画像の変更ボタンが押せない場合があるのを修正しました。**

2018/06/06<br>
**決済画像をメディアから選択できるようにしました**<br>
**テストモードを導入しました**

