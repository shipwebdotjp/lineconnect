import clsx from 'clsx';
import Heading from '@theme/Heading';
import styles from './styles.module.css';
import Translate, { translate } from '@docusaurus/Translate';
import useBaseUrl from '@docusaurus/useBaseUrl'; // useBaseUrlフックをインポート

const FeatureList = [
  {
    title: translate({
      id: 'homepage.feature.title1',
      description: 'Title of the first feature',
      defaultMessage: 'Quickly deliver updates',
    }),
    // SvgからImgUrlに変更し、WEBP画像のパスを文字列で指定
    ImgUrl: '/img/feature/updatenotification.webp',
    description: translate({
      id: 'homepage.feature.description1',
      description: 'Description of the first feature',
      defaultMessage: 'You can send article update notifications to your LINE official account friends. Send eye-catching and eye-catching messages.',
    }),
  },
  {
    title: translate({
      id: 'homepage.feature.title2',
      description: 'Title of the second feature',
      defaultMessage: 'For busy people like you',
    }),
    // SvgからImgUrlに変更し、WEBP画像のパスを文字列で指定
    ImgUrl: '/img/feature/trigger.webp', // trigger.webp が存在すると仮定
    description: translate({
      id: 'homepage.feature.description2',
      description: 'Description of the second feature',
      defaultMessage: 'You can have LINE automatically respond with a message of your choice triggered by sending keywords or rich menu taps.',
    }),
  },
  {
    title: translate({
      id: 'homepage.feature.title3',
      description: 'Title of the third feature',
      defaultMessage: 'Let AI do the responding for you',
    }),
    // SvgからImgUrlに変更し、WEBP画像のパスを文字列で指定
    ImgUrl: '/img/feature/aiassistant.webp',
    description: translate({
      id: 'homepage.feature.description3',
      description: 'Description of the third feature',
      defaultMessage: 'You can have it automatically respond to inquiries or perform actions in conjunction with ChatGPT.',
    }),
  },
];

// Featureコンポーネントのprops名を Svg から ImgUrl に変更
function Feature({ ImgUrl, title, description }) {
  // useBaseUrlフックを使って、サイトのベースURLを考慮した画像パスを生成
  const imgUrlAbsolute = useBaseUrl(ImgUrl);
  return (
    <div className={clsx('col col--4')}>
      <div className="text--center">
        {/* Svgコンポーネントの代わりに img タグを使用 */}
        {/* srcには useBaseUrl で生成した絶対パスを指定 */}
        {/* className は styles.featureImage など、別途CSSで定義したクラス名を指定 */}
        <img className={styles.featureImage} src={imgUrlAbsolute} alt={title} />
      </div>
      <div className="text--center padding-horiz--md">
        <Heading as="h3">{title}</Heading>
        <p>{description}</p>
      </div>
    </div>
  );
}

export default function HomepageFeatures() {
  return (
    <section className={styles.features}>
      <div className="container">
        <div className="row">
          {FeatureList.map((props, idx) => (
            <Feature key={idx} {...props} />
          ))}
        </div>
      </div>
    </section>
  );
}