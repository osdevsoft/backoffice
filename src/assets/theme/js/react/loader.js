class EntityItem extends React.Component
{
    constructor(props)
    {
        super(props);
        this.props = props;
    }

    render()
    {
        return(
            <tr>
                <td>{this.props.name}</td>
            </tr>
        );
    }

}

class EntityList extends React.Component
{
    constructor(props)
    {
        super(props);
        this.props = props;

        fetch('http://api.myproject.sandbox/api/post').then(
            data => console.log(data)
        );
    }

    render()
    {
        return(
            <div>
                Estamos en entidad {this.props.entity}
                <table>
                    <tbody>
                        <EntityItem name='post1'/>
                    </tbody>
                </table>
            </div>


        );
    }

};

ReactDOM.render(<EntityList entity="post" />, document.getElementById('entity_list'));
