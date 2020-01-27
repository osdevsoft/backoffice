export default class EntityList extends React.Component
{
    constructor(props)
    {
        super(props);
        this.props = props;
    }

    render()
    {
        return(
            <div>
                Estamos en entidad {this.props.entity}
            </div>
        );
    }

};