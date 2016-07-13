
@section('script')
   <script type="text/javascript">
      var data = '{!! $data['content'] !!}';
      var $content = $(".content");

      $(function() {
          $content.html(data);
      });
   </script>
@stop