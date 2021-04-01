import React from 'react';

export default ({ shortcodeConfig, formValues, initialValues }) => {
  const currentState = shortcodeConfig.states.find(
    stateConfig => stateConfig.and.every(condition =>
      Object.entries(condition).every(([fieldName, [operator, expectedValue]]) => {
        switch (operator) {
          case '=':
            return formValues[fieldName] === expectedValue;
          default:
            return false;
        }
      })
    )
  ).name;
  const template = shortcodeConfig.templates[currentState];

  const shortcode = template.replaceAll(
    /%([a-zA-Z0-9_-]*)%/g,
    (_, fieldName) => {
      return formValues[fieldName] || initialValues[fieldName];
    }
  );

  return <pre style={{overflowWrap: 'break-word'}}>{shortcode}</pre>;
};
