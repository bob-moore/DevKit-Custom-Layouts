import { Fragment, Component, createRef } from "@wordpress/element";
import {__} from "@wordpress/i18n";
import Group from './group';

export default class extends Component {
    constructor(props)
    {
        super(props);

        for ( let i in this.props.meta.conditions )
        {
            this.props.meta.conditions[i].index = this._itemKey(i);
        }

        this.state = {
            conditions : this.props.meta.conditions,
        };
    }
    _add = ( e ) => {
        e.preventDefault();
        let group = {
            display: 1,
            index : this._itemKey( this.state.conditions.length ),
            context : [
                {
                    view : '__return_true',
                    comparison : '=',
                    subtype : '',
                    id : '',
                    deps : []
                }

            ]
        };

        let conditions = this.state.conditions;
        conditions.push( group );
        this.setState( { conditions : conditions } );
    }
    _remove = (context, i) => {
        if ( ! context.length )
        {
            let conditions = this.state.conditions;
            conditions.splice(i, 1);
            this.setState( { conditions : conditions } );
        }

    }
    _itemKey = (index) => {
        return btoa( + index + '_' + Date.now() );
    }
    render()
    {
        return (
            <Fragment>
                { ! this.state.conditions.length &&
                    <div className="field-group">
                        <div className="field">
                            <div className="devkit-notice devkit-notice-error">
                                <p>{__('This layout has no display conditions.', 'devkit_layouts')} <a href='#' onClick={this._add}>{__('Create one now', 'devkit_layouts')}</a></p>
                            </div>
                        </div>
                    </div>
                }
                { this.state.conditions.map( ( group, i ) => {
                    return (
                        <Fragment key={group.index}>
                            { i > 0 &&
                                <span className="group-label">OR</span>
                            }
                            <Group
                                ref={this.state.conditions[i].ref}
                                group={i}
                                display={group.display}
                                context={group.context}
                                fields={this.props.fields}
                                meta={this.props.meta}
                                onRemove={this._remove}
                            />
                        </Fragment>
                    )
                })}
                { this.state.conditions.length > 0 &&
                    <div className="field-group">
                        <div className="field push-right">
                            <a href="#" className="add group-control" onClick={this._add}>{__('New Group', 'devkit_layout')}</a>
                        </div>
                    </div>
                }
            </Fragment>
        );
    }
}