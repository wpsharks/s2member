import React, { Component } from 'react';
import { Formik, Form as FormikForm, Field } from 'formik';
import _isObject from 'lodash.isobject';

class Form extends Component {
  constructor(props) {
    super(props);

    const initialValues = {};
    for (const [fieldName, fieldConfig] of Object.entries(props.config.fields)) {
      initialValues[fieldName] = fieldConfig.initialValue || '';
    }

    this.state = {
      initialValues,
    };
  }

  renderFormTemplate({ key, form, template }) {
    const inBetweenText = template.split(/%[a-zA-Z0-9_-]*%/g);
    const fields = [...template.matchAll(/%([a-zA-Z0-9_-]*)%/g)].map(a => a[1]);

    let inBetweenTextIndex = 0;
    let fieldsIndex = 0;

    const result = [];

    for (let i = 0; i < inBetweenText.length + fields.length; i += 1) {
      // If the number is odd then it's an inBetweenText, if it's even it's a fields.
      if ((i + 1) % 2 /* is odd? */) {
        result.push(<span key={i}>{inBetweenText[inBetweenTextIndex]}</span>);
        inBetweenTextIndex += 1;
      } else {
        const fieldName = fields[fieldsIndex];
        const fieldConfig = form.fields[fieldName];

        switch (fieldConfig.type) {
          case 'text':
          case 'price':
            result.push(<Field key={fieldName} type="text" name={fieldName} />);
            break;
          case 'checkbox':
            result.push(<Field key={fieldName} type="checkbox" name={fieldName} />);
            break;
          case 'dropdown':
            result.push((
              <Field key={fieldName} as="select" name={fieldName}>
                {fieldConfig.options.map(([name, value], i) => (
                  <option key={i} value={value}>{name}</option>
                ))}
              </Field>
            ));
            break;
          case 'level':
            result.push((
              <Field key={fieldName} as="select" name={fieldName}>
                {this.props.levels.map(([name, value], i) => (
                  <option key={i} value={value}>{name}</option>
                ))}
              </Field>
            ));
            break;
          default:
            break;
        }

        fieldsIndex += 1;
      }
    }

    return <p key={key}>{result}</p>;
  }

  /**
   * Generates the shortcode (a string) based on the configuration and the values from the form.
   * @param config
   * @param values
   * @return String
   */
  generateShortcode(config, values) {
    const shortcodeTemplate = config.shortcode.template;

    let result = shortcodeTemplate.replaceAll(
      /%([a-zA-Z0-9_-]*)%/g,
      (placeholder, fieldName) => {
        return values[fieldName] || this.state.initialValues[fieldName];
      }
    );

    return result;
  }

  checkHasTemplateConditionals(group) {
    return group.length > 1 && _isObject(group[0]) && _isObject(group[0].if);
  }

  testTemplateConditionals(conditionals, values) {
    const valuesToCheck = Object.keys(conditionals.if);
    return valuesToCheck.every(key => values[key] === conditionals.if[key]);
  }

  render() {
    const config = this.props.config;
    return (
      <div className="s2x_shortcodegenerator_form">
        <Formik initialValues={this.state.initialValues}>
          {formikProps => (
            <FormikForm>
              {config.formTemplate.map((group, key) => {
                if (this.checkHasTemplateConditionals(group)) {
                  if (this.testTemplateConditionals(group[0], formikProps.values)) {
                    return this.renderFormTemplate({ key, form: config, template: group[1] });
                  }
                } else {
                  return this.renderFormTemplate({ key, form: config, template: group[0] });
                }
              })}
            </FormikForm>
          )}
        </Formik>
      </div>
    );
  }
}

export default Form;
