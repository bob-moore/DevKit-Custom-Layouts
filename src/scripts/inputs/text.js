import { Fragment, Component } from "@wordpress/element";

export default class extends Component
{
    constructor(props)
    {
        super(props);

        this.state = {
            value : this.props.value,
        };

        this.debounce = null;
    }
    _update = ( event ) =>
    {
        this.setState( { value : event.target.value }, () =>
            {
                this.props.onChange( event.target.value );
            }
        );
    }
    _debounce = ( callback, event ) => {
        clearTimeout(this.debounce);
        this.debounce = setTimeout( () => {
            this.debounce = null;
            callback( event );
        }, 300 );
    }
    render()
    {
        return (
            <input
                type="text"
                name={this.props.name}
                defaultValue={this.state.value}
                onBlur={this._update}
                onChange={(e) => { this._debounce( this._update, e ) }}
                placeholder={this.props.placeholder ?? ''}
            />
        )
    }
}