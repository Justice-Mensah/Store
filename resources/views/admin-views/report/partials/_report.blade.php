<div class="table-responsive datatable-custom">
    <table id="datatable"
           style="width: 100%">
        <tbody id="set-rows">
        <tr>
            <td>{{trans('messages.#')}} </td>
            <td>{{trans('messages.order')}}</td>
            <td>{{trans('messages.date')}}</td>
            <td>{{trans('messages.qty')}}</td>
            <td>{{trans('messages.customer')}}</td>
            <td>{{trans('messages.amount')}}</td>
        </tr>
        @foreach($data as $key=>$row)
            <tr>
                <td class="pull-right">
                    {{$key+1}}
                </td>
                <td class="table-column-pl-0">
                    <a href="{{route('admin.orders.details',['id'=>$row['order_id']])}}">{{$row['order_id']}}</a>
                </td>
                <td>{{date('d M Y',strtotime($row['date']))}}</td>
                <td>{{$row['quantity']}}</td>
                <td>
                    @if($row['customer'])
                        <a class="text-body text-capitalize"
                           href="{{--{{route('admin.customer.view',[$row['customer']['id']])}}--}}">{{$row['customer']->f_name.' '.$row['customer']->l_name}}</a>
                    @else
                        <label
                            class="badge badge-danger">{{trans('messages.invalid')}} {{trans('messages.customer')}} {{trans('messages.data')}}</label>
                    @endif
                </td>
                <td>{{$row['price'] ." ". \App\CentralLogics\Helpers::currency_symbol()}}</td>
            </tr>

        @endforeach
        </tbody>
    </table>
</div>
