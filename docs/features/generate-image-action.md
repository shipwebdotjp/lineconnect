## Summary

  画像生成は gpt-image-2 を使う専用 Action として追加し、OpenAI の画像出力は base64 で返る前提でローカル保存します。OpenAI 公式 docs では GPT image 系モデルは画像出力を返し、size / quality / background に auto が使え、出力は base64 ベースで扱う想定です。

## Key Changes

  - Action の戻り値は response_mode を持つ汎用契約にする。
  - response_mode = direct のときは OpenAi.php 側で LLM 再呼び出しを止め、Action が返した messages をそのまま最終返信に積む。
  - 通常の tool call は現状どおり LLM へ戻す。
  - 画像生成 Action は messages に ImageMessageBuilder を返し、LLM の説明文は送信しない。
  - 画像ファイルは添付ファイル保存先とは分離し、公開用サブディレクトリに保存する。
  - 保存先の実装対象は主に src/Bot/Provider/OpenAi.php と src/Bot/File.php で、Action 定義は src/Action/Definitions/ に追加する。

## Decision Details

  - Action result envelope
      - success: 実行成否
      - response_mode: direct or llm
      - messages: LINE 返信に積むビルダー配列
      - data: 保存パスやメタ情報などの補助情報
  - 画像保存パス
      - root: uploads/lineconnect/generated/
      - directory: {channel_prefix}/{Y/m}/image/
      - filename: gpt-image-2-{Ymd-His}-{uuid8}.png
      - MIME / 拡張子: 固定で image/png / .png
  - 生成パラメータ固定値
      - model = gpt-image-2
      - size = auto
      - quality = auto
      - background = auto
      - output_format = png
  - 返信ポリシー
      - 成功時: 画像だけ返す
      - 失敗時: 短いエラーテキストを返す
  - 保持方針
      - 生成画像は削除せず永続保存する
      - 既存の getMessageContent() 保存物と公開画像は分離する

## Test Plan

  - 画像生成 Action が gpt-image-2 に固定パラメータでリクエストすること。
  - 画像保存先が uploads/lineconnect/generated/{channel_prefix}/{Y/m}/image/ になること。
  - 生成結果が .png として保存され、公開URLから ImageMessageBuilder を作れること。
  - response_mode = direct のとき OpenAi.php が再LLMせず、その messages を返すこと。
  - 失敗時は画像を返さず、テキストエラーに落ちること。

## Assumptions

  - 画像生成は今後の他 Action へも流用できるよう、response_mode を新設する。
  - 画像生成の説明文より、画像そのものを優先する。
  - 画像保存は永続運用で、クリーンアップは今回の範囲外とする。