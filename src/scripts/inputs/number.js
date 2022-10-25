import { Component } from "@wordpress/element";

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
                type="number"
                name={this.props.name}
                defaultValue={this.state.value}
                min={this.props.min ?? '0'}
                max={this.props.max ?? ''}
                step={this.props.step ?? '1'}
                onBlur={this._update}
                onChange={(e) => { this._debounce( this._update, e ) }}
            />
        )
    }
}