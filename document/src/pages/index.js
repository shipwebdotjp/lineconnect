import clsx from 'clsx';
import Link from '@docusaurus/Link';
import Layout from '@theme/Layout';
import HomepageFeatures from '@site/src/components/HomepageFeatures';
import Translate, { translate } from '@docusaurus/Translate';
import Heading from '@theme/Heading';
import styles from './index.module.css';

const INTRO_VIDEO_URL = 'https://gpt.shipweb.jp/assets/lineconnect_intro.mp4';
const INTRO_VIDEO_POSTER_URL = 'https://gpt.shipweb.jp/assets/lineconnect_intro.png';

function HomepageHeader() {
  return (
    <header className={clsx('hero hero--primary', styles.heroBanner)}>
      <div className="container">
        <Heading as="h1" className="hero__title">
          <Translate id="homepage.title" description="Title of the home page">LINE Connect Document</Translate>
        </Heading>
        <p className="hero__subtitle">
          <Translate id="homepage.tagline" description="Tagline of the home page">Plugin to link WordPress and LINE Official Account</Translate>
        </p>
        <div className={styles.buttons}>
          <Link
            className="button button--primary button--lg"
            to="/docs/intro">
            <Translate id="homepage.start" description="Start button on homepage">Start using the LINE Connect</Translate>
          </Link>
          <Link
            className="button button--secondary button--lg"
            to="https://github.com/shipwebdotjp/lineconnect/releases/latest">
            <Translate id="homepage.download" description="Download button on homepage">Download</Translate>
          </Link>
        </div>
      </div>
    </header>
  );
}

function HomepageIntroVideo() {
  return (
    <section className={styles.videoSection} aria-label="Introduction video">
      <div className="container">
        <div className={styles.videoWrap}>
          <div className={styles.videoFrame}>
            <video
              className={styles.video}
              controls
              playsInline
              preload="metadata"
              poster={INTRO_VIDEO_POSTER_URL}
              aria-label="LINE Connect introduction video">
              <source src={INTRO_VIDEO_URL} type="video/mp4" />
              Your browser does not support the video tag.
            </video>
          </div>
        </div>
      </div>
    </section>
  );
}

export default function Home() {
  return (
    <Layout
      title={translate({
        id: 'homepage.title',
        description: 'Title of the home page',
        defaultMessage: 'LINE Connect Document',
      })}
      description="Description will go into a meta tag in <head />">
      <HomepageHeader />
      <main>
        <HomepageIntroVideo />
        <HomepageFeatures />
      </main>
    </Layout>
  );
}
