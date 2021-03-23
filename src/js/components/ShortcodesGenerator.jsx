import React, { Component } from 'react';
import { Formik, Form, Field } from 'formik';
import _isObject from 'lodash.isobject';

/**
 * forms []
 * {
 *   name,
 *   fields {
 *     "key": {
 *       type = text|price|levels|dropdown,
 *       options = [["key", "value"], ...] when dropdown
 *     }
 *   },
 * }
 */

class ShortcodesGenerator extends Component {
  constructor(props) {
    super(props);

    this.state = {
      initialValues: {},
    };

    this.renderFieldGroup = this.renderFieldGroup.bind(this);
  }

  onSubmit() {}

  renderFieldGroup({ key, form, template }) {
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
        const currentField = form.fields[fieldName];

        switch (currentField.type) {
          case 'text':
          case 'price':
            result.push(<Field key={fieldName} type="text" name={fieldName} />);
            break;
          case 'dropdown':
            result.push((
              <Field key={fieldName} as="select" name={fieldName}>
                {currentField.options.map(([name, value], i) => (
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

  render() {
    return (
      <div className="s2x_shortcodes_generator">
        <div className="s2x_shortcodes_generator__form">
          {this.props.config.forms.map(form => (
            <Formik key={form.name} initialValues={this.state.initialValues} onSubmit={this.onSubmit}>
              {formikProps => (
                <Form>
                  {form.fieldGroups.map((group, i) => {
                    const key = i;
                    if (_isObject(group[0]) && _isObject(group[0].if)) {
                      const valuesToCheck = Object.keys(group[0].if);
                      if (valuesToCheck.every(key => formikProps.values[key] === group[0].if[key])) {
                        return this.renderFieldGroup({ key, form, template: group[1] });
                      }
                    } else {
                      return this.renderFieldGroup({ key, form, template: group[0] });
                    }
                  })}
                </Form>
              )}
            </Formik>
          ))}
        </div>
    </div>
    );
  }
}

export default ShortcodesGenerator;
