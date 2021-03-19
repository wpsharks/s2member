import React, { Component } from 'react';
import { render } from 'react-dom';

// Inject our app into the DOM
class App extends Component {
    render() {
        return <h1>Hello, world!</h1>;
    }
}

render(
  <App />,
  document.getElementById('s2x-shortcode-generator'),
);
