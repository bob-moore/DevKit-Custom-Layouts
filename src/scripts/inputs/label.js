import { Component } from "@wordpress/element";

export default class extends Component
{
    constructor(props)
    {
        super(props);

        this.state = {
            display : this.props.display ?? true,
        };
    }
    render()
    {
        return (
            <label
                htmlFor={this.props.for}
                className={this.state.display ? 'input-label' : 'input-label screen-reader-text'}>
                {this.props.value}
            </label>
        )
    }
}