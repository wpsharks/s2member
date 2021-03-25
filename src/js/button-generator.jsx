import React from 'react';
import { render } from 'react-dom';

import ShortcodeGenerator from './components/ShortcodeGenerator/index.jsx';

const ButtonGenerator = () => (
  <ShortcodeGenerator config={window.__s2x_button_generator_config.generator_config} levels={window.__s2x_button_generator_config.levels}/>
);

render(
  <ButtonGenerator />,
  document.getElementById('s2x-button-generator'),
);
