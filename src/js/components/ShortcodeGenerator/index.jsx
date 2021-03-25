import React, { Component } from 'react';

import Questions from './Questions.jsx';
import ShortcodesGenerator from "../ShortcodesGenerator.jsx";

class ShortcodeGenerator extends Component {
  constructor(props) {
    super(props);

    this.state = {
      form: null,
    };

    this.setForm = this.setForm.bind(this);
  }

  setForm(form) {
    this.setState({ form });
  }

  render() {
    return (
      <div className="s2x_shortcodegenerator">
        <Questions
          questions={this.props.config.questions}
          answersToForm={this.props.config.answersToForm}
          setForm={this.setForm}
        />

        {this.state.form !== null &&
          <ShortcodesGenerator
            config={this.props.config.forms[this.state.form]}
            levels={this.props.levels}
          />
        }
      </div>
    )
  }
}

export default ShortcodeGenerator;
