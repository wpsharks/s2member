import React from 'react';
import { render } from 'react-dom';

import ShortcodeGenerator from './components/ShortcodeGenerator/index.jsx';

const ButtonGenerator = () => (
  <ShortcodeGenerator
    config={window.__s2x_button_generator_config.generator_config}
    levels={window.__s2x_button_generator_config.levels}
    domain={'<?php echo c_ws_plugin__s2member_utils_strings::esc_js_sq (esc_attr ($_SERVER["HTTP_HOST"])); ?>'}
  />
);

render(
  <ButtonGenerator />,
  document.getElementById('s2x-button-generator'),
);
