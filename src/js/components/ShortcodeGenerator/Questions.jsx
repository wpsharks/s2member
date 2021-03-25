import React, { Component } from 'react';

const generateAnswersState = questions => {
  const answers = {};

  for (const question of questions) {
    answers[question.name] = question.initialValue || null;
  }

  return answers;
};

class Questions extends Component {
  constructor(props) {
    super(props);

    this.state = {
      answers: generateAnswersState(props.questions),
    };

    this.onChange = this.onChange.bind(this);
  }

  onChange(name, e) {
    this.setState({
      answers: {
        ...this.state.answers,
        [name]: e.target.value,
      },
    });
  }

  componentDidUpdate(prevProps, prevState) {
    if (
      // If the prev state isn't the same as the current
      JSON.stringify(Object.values(this.state.answers)) !== JSON.stringify(Object.values(prevState.answers)) &&
      // If all the questions have answers
      Object.values(this.state.answers).every(a => a !== null)
    ) {
      const match = this.props.answersToForm.find(({ answers }) =>
        // If the amount of answers in the answersToForm object is the same as
        // the amount of correct answers from the user, then it's a match.
        Object.keys(answers).length ===
        Object.entries(answers)
          .filter(([question, correctAnswer]) => this.state.answers[question] === correctAnswer)
          .length
      );

      // We check this so as to not cause an infinite update loop by calling
      // this.props.setForm() when we don't intend to or with the wrong value.
      if (match === void 0) {
        this.props.setForm(null);
      } else {
        this.props.setForm(match.form);
      }
    }
  }

  render() {
    return (
      <div className="s2x_shortcodegenerator_questions">
        {this.props.questions.map(({ text, options, name, initialValue }) => (
          <p key={name}>
            {text}
            <select value={this.state.answers[name] || initialValue} onChange={this.onChange.bind(this, name)}>
              {initialValue === void 0 && <option value={null}>Select an option</option>}
              {options.map(([text, value]) => (
                <option key={value} value={value}>{text}</option>
              ))}
            </select>
          </p>
        ))}
      </div>
    );
  }
}

export default Questions;
