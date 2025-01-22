var vue = new Vue({
  el: '#content',
  data: {
    showAllColumns: false,
    virtualSellers: [],
    clients: [],
    filters:{
      text: '',
      virtualManager: '',
      day: '',
    },
    formItinerary:{
      pass: 'getItinerary',
      rute: '',
    },
    loaders:{
      list: false,
    },
    totals:{
      lu: 0,
      ma: 0,
      mi: 0,
      ju: 0,
      vi: 0,
      sa: 0,
    },
    sortOrder: '',
    sap_api: '',
  },
  mounted(){
    this.getVirtualSellers()
  },
  computed: {
    filteredClients: function(){

      let tempClients = []
      tempClients = this.clients.filter(client => {
        return client?.NAME1?.toLowerCase().includes(this.filters.text.toLowerCase()) || 
        client?.KUNNR?.toLowerCase().includes(this.filters.text.toLowerCase()) || 
        client?.NAME2?.toLowerCase().includes(this.filters.text.toLowerCase()) || 
         client?.LZONE?.toLowerCase().includes(this.filters.text.toLowerCase())  
      })

      tempClients = tempClients.filter(client => {
        return client.LU.includes(this.filters.virtualManager) || 
        client.MA.includes(this.filters.virtualManager) || 
        client.MI.includes(this.filters.virtualManager) || 
        client.JU.includes(this.filters.virtualManager) || 
        client.VI.includes(this.filters.virtualManager) || 
        client.SA.includes(this.filters.virtualManager) 
      })

      if(this.filters.day != ''){
        tempClients = tempClients.filter(client => {
          return client[this.filters.day]
        })
      }

      // Totales
      this.totals.lu = tempClients.filter(client => client.LU != '').length
      this.totals.ma = tempClients.filter(client => client.MA != '').length
      this.totals.mi = tempClients.filter(client => client.MI != '').length
      this.totals.ju = tempClients.filter(client => client.JU != '').length
      this.totals.vi = tempClients.filter(client => client.VI != '').length
      this.totals.sa = tempClients.filter(client => client.SA != '').length

      //console.log(tempClients)
      return tempClients
    }
  },
  methods:{

    getVirtualSellers: function(){
      var config = {
        method: 'get',
        url: 'endpoint.php?pass=getVirtualSellers',
        headers: { }
      };

      axios(config)
      .then(function (response) {
        console.log(response.data);
        vue.virtualSellers = response.data[0]
        vue.sap_api = response.data[1]
      }).catch(function (error) {
        console.log(error);
      });
 
    },
    // Consulta la lista de clientes desde SAP
    getClients: function(){
    /*
      this.clients = []
      const ruta = this.formItinerary.rute

        this.loaders.list = true
        var config = {
          method: 'get',
          url: `endpoint.php?pass=getClients&rute=${ruta}`,
          headers: { }
        };
        axios(config)
        .then(function (response) {
          // console.log(response.data);
          vue.loaders.list = false
          // vue.clients = response.data
          vue.getVirtualItinerary(response.data)
        })
        .catch(function (error) {
          vue.loaders.list = false
          console.log(error);
        });
      */

       
       this.clients = []
       const ruta = this.formItinerary.rute
       let url = ''
        
       // GET URL FROM ENV
       const api = this.sap_api

       if (this.formItinerary.rute == 'TODOS') {
        url = `${api}/tablefs?table=KNA1&where=SORTL%20LIKE%20%27DET%%27OR%20SORTL%20LIKE%20%27DSM%%27&fields=KUNNR,NAME1,NAME2,SORTL,LZONE,STRAS,TELF1`
       } else {
        url = `${api}/tablefs?table=KNA1&where=SORTL%20=%20%27${ruta}%27&fields=KUNNR,NAME1,NAME2,SORTL,LZONE,STRAS,TELF1`
       }
       //console.log(url)
       
       if(ruta != ''){
         this.loaders.list = true
         var config = {
           method: 'get',
           url: url,
           headers: { }
         };
         axios(config)
         .then(function (response) {
           vue.loaders.list = false
           // vue.clients = response.data
           vue.getVirtualItinerary(response.data)
         })
         .catch(function (error) {
           vue.loaders.list = false
           console.log(error);
         });
       }

    },
    // Consulta el itinerario actual de ORACLE
    getVirtualItinerary: function(clients){
        
        const ruta = this.formItinerary.rute
        const day = this.filters.day
        const field = 'ORDEN_'+day

        if(ruta != ''){
  
          this.loaders.list = true
          var config = {
            method: 'get',
            url: `endpoint.php?pass=getItinerary&rute=${ruta}&orden=${field}`,
            headers: { }
          };

          axios(config)
          .then(function (response) {
            console.log(response.data);
            vue.loaders.list = false
            vue.asignItineraries(clients, response.data)
          })
          .catch(function (error) {
            vue.loaders.list = false
            console.log(error);
          });

        }
    },
    setOrder(client, field){
      const kunnr = client.KUNNR
      const value = client[field]

      axios.get(`endpoint.php?pass=setOrder&kunnr=${kunnr}&field=${field}&value=${value}`)
      .then(response => {
        if (response.data && response.data['exito'] && response.data['exito'] == 1) {
          console.log(response.data)
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo actualizar el orden para el cliente '+client.NAME1 + ' ' + client.NAME2,
          })
          this.getClients()
        }
      })
      .catch(error => {
        console.log(error)
      })
    },
    // Asigna los itinerarios a los clientes
    asignItineraries: function(clients, itinerary){
      clients.forEach(client => {

        const temp = itinerary ? itinerary?.find(virtual => virtual.CODIGO == client.KUNNR) : null
        
        client['LU'] = temp && temp['LU'] ? temp['LU'] : ''
        client['MA'] = temp && temp['MA'] ? temp['MA'] : ''
        client['MI'] = temp && temp['MI'] ? temp['MI'] : ''
        client['JU'] = temp && temp['JU'] ? temp['JU'] : ''
        client['VI'] = temp && temp['VI'] ? temp['VI'] : ''
        client['SA'] = temp && temp['SA'] ? temp['SA'] : ''

        client['ORDEN_LU'] = temp && temp['ORDEN_LU'] ? temp['ORDEN_LU'] : ''
        client['ORDEN_MA'] = temp && temp['ORDEN_MA'] ? temp['ORDEN_MA'] : ''
        client['ORDEN_MI'] = temp && temp['ORDEN_MI'] ? temp['ORDEN_MI'] : ''
        client['ORDEN_JU'] = temp && temp['ORDEN_JU'] ? temp['ORDEN_JU'] : ''
        client['ORDEN_VI'] = temp && temp['ORDEN_VI'] ? temp['ORDEN_VI'] : ''
        client['ORDEN_SA'] = temp && temp['ORDEN_SA'] ? temp['ORDEN_SA'] : ''
      });

      this.clients = clients
    },
    // Guarda un campo del itinerario virtual en ORACLE
    setVirtualItinerary: function(day, code, value){

      const ruta = this.formItinerary.rute
      if(day && code && ruta != ''){

        var config = {
          method: 'get',
          url: `endpoint.php?pass=setVirtualItinerary&day=${day}&code=${code}&virtualSeller=${value}&ruta=${ruta}`,
          headers: { }
        };
  
        axios(config)
        .then(function (response) {
          // console.log(response.data);

          if (response.data[0] && response.data[0].exito == 1) {
            vue.showToastSwal('success')
          }else {
            vue.clearDay(day, code)
            vue.showToastSwal('error')
          }

        }).catch(function (error) {
          console.log(error);
        });

      }
    },
    clearDay: function(day, code){
      let registry = this.clients.find(client => client.KUNNR == code)
      registry[day] = ''
    },
    showToastSwal: function(type){
      const Toast = Swal.mixin({
        toast: true,
        position: 'top-right',
        iconColor: 'white',
        customClass: {
          popup: 'colored-toast'
        },
        showConfirmButton: false,
        timer: 1500,
        timerProgressBar: true
      })

      if(type == 'success'){
        Toast.fire({
          icon: 'success',
          title: 'Hecho!'
        })
      } else if (type == 'error'){
        Toast.fire({
          icon: 'error',
          title: 'Error!'
        })
      }

    },
    orderClients: function(){
      const day = this.filters.day
      const field = 'ORDEN_'+day
      this.sortOrder = this.sortOrder == 'asc' ? 'desc' : 'asc'

      if (!day || day == '' && !this.filters.virtualManager) {
        return
      }

      this.clients = this.clients.sort((a, b) => {
        if(this.sortOrder == 'asc'){
          return a[field] - b[field]
        } else {
          return b[field] - a[field]
        }
      })
    }

  }
});

