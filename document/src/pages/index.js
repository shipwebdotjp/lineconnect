import clsx from 'clsx';
import Link from '@docusaurus/Link';
import useDocusaurusContext from '@docusaurus/useDocusaurusContext';
import Layout from '@theme/Layout';
import HomepageFeatures from '@site/src/components/HomepageFeatures';
import Translate, { translate } from '@docusaurus/Translate';
import Heading from '@theme/Heading';
import styles from './index.module.css';

function HomepageHeader() {
  const { siteConfig } = useDocusaurusContext();
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

export default function Home() {
  const { siteConfig } = useDocusaurusContext();
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
        <HomepageFeatures />
      </main>
    </Layout>
  );
}
