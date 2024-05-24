import clsx from 'clsx';
import Heading from '@theme/Heading';
import styles from './styles.module.css';
import Translate, {translate} from '@docusaurus/Translate';

const FeatureList = [
  {
    title: translate({
      id: 'homepage.feature.title1',
      description: 'Title of the first feature',
      defaultMessage: 'Quickly deliver updates',
    }),
    Svg: require('@site/static/img/article.svg').default,
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
    Svg: require('@site/static/img/communication.svg').default,
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
    Svg: require('@site/static/img/synapse.svg').default,
    description: translate({
      id: 'homepage.feature.description3',
      description: 'Description of the third feature',
      defaultMessage: 'You can have it automatically respond to inquiries or perform actions in conjunction with ChatGPT.',
    }),
  },
];

function Feature({Svg, title, description}) {
  return (
    <div className={clsx('col col--4')}>
      <div className="text--center">
        <Svg className={styles.featureSvg} role="img" />
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
