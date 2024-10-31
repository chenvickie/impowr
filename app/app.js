import Application from '@ember/application';
import Resolver from './resolver';
import loadInitializers from 'ember-load-initializers';
import config from './config/environment';

export default class App extends Application {
  modulePrefix = config.modulePrefix;
  podModulePrefix = config.podModulePrefix;
  Resolver = Resolver;
  customEvents = {
    paste: 'paste'  //add suport for the paste event
  }
}

loadInitializers(App, config.modulePrefix);
