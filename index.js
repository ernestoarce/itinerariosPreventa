var vue = new Vue({
  el: '#content',
  data: {
    urlAPI: 'https://calidad-api-sap-crm.yes.com.sv',
    showAllColumns: false,
    virtualSellers: [],
    deliverer: [],
    clients: [],
    filters: {
      text: '',
      virtualManager: '',
      day: '',
    },
    formItinerary: {
      pass: 'getItinerary',
      rute: '',
    },
    loaders: {
      list: false,
      saveCRM: false,
      getClients: false,
    },
    totals: {
      lu: 0,
      ma: 0,
      mi: 0,
      ju: 0,
      vi: 0,
      sa: 0,
    },
    sortOrder: '',
    sap_api: 'http://192.168.101.125:8080',
    dragClient: {},
    dropClient: {},
    forCRM: false,
    selectorBusquedaSAP: '',
    buscarCliente: '',
    rutaSeleccionada: '',
    loadingSAPClientes: false,
    clientesTemporales: [],

    debounceTimeout: null,
    currentField: '',
    currentKunnr: '',
  },
  async mounted() {
    this.getVirtualSellers();

    const urlParams = new URLSearchParams(window.location.search);
    const o = urlParams.get('o');
    if (o==='crm') {
      this.forCRM = true;
    }

    await this.getClients();
  },
  computed: {
    filteredClients() {
      let tempClients = this.clients.filter(client => {
        const text = this.filters.text.toLowerCase();
        return (
          client?.NAME1?.toLowerCase().includes(text) ||
          client?.KUNNR?.toLowerCase().includes(text) ||
          client?.NAME2?.toLowerCase().includes(text) ||
          client?.LZONE?.toLowerCase().includes(text)
        );
      });

      if (!this.forCRM && this.formItinerary.rute && this.formItinerary.rute !== 'TODOS') {
        tempClients = tempClients.filter(client => client.SORTL === this.formItinerary.rute);
      }

      if (this.filters.virtualManager) {
        tempClients = tempClients.filter(client => client.PREVENDEDOR.includes(this.filters.virtualManager));
      }

      if (this.filters.day) {
        tempClients = tempClients.filter(client => client[this.filters.day]);
      }

      this.totals.lu = tempClients.filter(client => client.LU).length;
      this.totals.ma = tempClients.filter(client => client.MA).length;
      this.totals.mi = tempClients.filter(client => client.MI).length;
      this.totals.ju = tempClients.filter(client => client.JU).length;
      this.totals.vi = tempClients.filter(client => client.VI).length;
      this.totals.sa = tempClients.filter(client => client.SA).length;

      return tempClients;
    },
  },
  methods: {
    setClientDetails(kunnr, field, value) {

      //if (this.currentField && this.currentKunnr && (this.currentField != field || this.currentKunnr != kunnr)) {
      //if (this.currentField != field || this.currentKunnr != kunnr) {
      //  this.updateClientDetails(kunnr, field, value);
      //}

      this.updateClientDetails(kunnr, field, value);
      this.currentField = field;
      this.currentKunnr = kunnr;
      
      //clearTimeout(this.debounceTimeout);
      //this.debounceTimeout = setTimeout(() => {
      //  this.updateClientDetails(kunnr, field, value);
      //}, 2000);
    },
    async updateClientDetails(kunnr, field, value) {
      try {
        const response = await axios.get(`postgres.php?endpoint=updateClientDetails&kunnr=${kunnr}&field=${field}&value=${value}`);
        console.log(response.data);
        if (response.data.exito === 1) {
          //this.showToastSwal('success');
          this.changeFieldColorTemporal(kunnr, field, 'notifgreen');
        } else {
          //this.showToastSwal('error');
          this.changeFieldColorTemporal(kunnr, field, 'notifred');
        }
      } catch (error) {
        this.changeFieldColorTemporal(kunnr, field, 'notifred');
        console.error(error);
      }
    },
    changeFieldColorTemporal(kunnr, field, className){
      document.getElementById(kunnr + '_' + field).classList.add(className);
      setTimeout(() => {
        document.getElementById(kunnr + '_' + field).classList.remove(className);
      }, 3000);
    },
    async getVirtualSellers() {
      try {
        const response = await axios.get('endpoint.php?pass=getVirtualSellers');
        this.virtualSellers = response.data[0];
        this.deliverer = response.data[0];

        // ORDER delieverer By TOUR_ID
        this.deliverer.sort((a, b) => (a.TOUR_ID > b.TOUR_ID) ? 1 : -1);

        if (this.forCRM) {
          this.virtualSellers = this.virtualSellers.filter(seller => seller.ID.startsWith('TEL'));
        } else {
          this.virtualSellers = this.virtualSellers.filter(seller => !seller.ID.startsWith('TEL'));
        }

        this.sap_api = this.sap_api || response.data[1];
      } catch (error) {
        console.error(error);
      }
    },
    async getClients() {
      this.clients = [];
      const ruta = this.formItinerary.rute;
      const api = this.sap_api;

      let itineraries = [];
      try {
        this.loaders.getClients = true;
        itineraries = await this.getAllItineraries() || [];
        //console.log(itineraries);

        if (itineraries.length === 0) {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se encontraron itinerarios',
          });
          return;
        }

        // get CODIGO from all itineraries
        let codes = itineraries.map(itinerary => itinerary.CODIGO);
        codes = [...new Set(codes)];
        //console.log(codes);

        // get all clients from API
        const response = await axios.post(this.urlAPI + '/datos-clientes/', {
          Clientes: codes
        });

        // merge clients with itineraries
        let clients = response.data;

        clients.forEach(client => {
          let temp = itineraries.find(virtual => virtual.CODIGO === client.KUNNR) || {};
          client.PREVENDEDOR = temp.PREVENDEDOR || '';
          client.LU = temp.LU ? parseInt(temp.LU) : '';
          client.MA = temp.MA ? parseInt(temp.MA) : '';
          client.MI = temp.MI ? parseInt(temp.MI) : '';
          client.JU = temp.JU ? parseInt(temp.JU) : '';
          client.VI = temp.VI ? parseInt(temp.VI) : '';
          client.SA = temp.SA ? parseInt(temp.SA) : '';

          client.ORDEN_LU = temp.ORDEN_LU || '';
          client.ORDEN_MA = temp.ORDEN_MA || '';
          client.ORDEN_MI = temp.ORDEN_MI || '';
          client.ORDEN_JU = temp.ORDEN_JU || '';
          client.ORDEN_VI = temp.ORDEN_VI || '';
          client.ORDEN_SA = temp.ORDEN_SA || '';
        }
        );

        this.clients = clients;

      } catch (error) {
        console.error(error);
      } finally {
        this.loaders.getClients = false;
      }
      
    },
    async getVirtualItinerary(clients) {
      const ruta = this.formItinerary.rute;
      const day = this.filters.day;
      const field = `ORDEN_${day}`;

      if (ruta) {
        this.loaders.list = true;
        try {
          const response = await axios.get(`endpoint.php?pass=getItinerary&rute=${ruta}&orden=${field}`);
          this.loaders.list = false;
          this.asignItineraries(clients, response.data);
        } catch (error) {
          this.loaders.list = false;
          console.error(error);
        }
      }
    },
    async setOrder(client, field) {
      const kunnr = client.KUNNR;
      const value = client[field];

      try {
        const response = await axios.get(`endpoint.php?pass=setOrder&kunnr=${kunnr}&field=${field}&value=${value}`);
        if (response.data && response.data.exito === 1) {
          console.log(response.data);
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: `No se pudo actualizar el orden para el cliente ${client.NAME1} ${client.NAME2}`,
          });
          this.getClients();
        }
      } catch (error) {
        console.error(error);
      }
    },
    asignItineraries(clients, itinerary) {
      clients.forEach(client => {

        let temp = {}
        if (itinerary != ''){
          temp = itinerary?.find(virtual => virtual.CODIGO === client.KUNNR) || {};
        } 

        client.PREVENDEDOR = temp.PREVENDEDOR || '';
        client.LU = temp.LU ? parseInt(temp.LU) : '';
        client.MA = temp.MA ? parseInt(temp.MA) : '';
        client.MI = temp.MI ? parseInt(temp.MI) : '';
        client.JU = temp.JU ? parseInt(temp.JU) : '';
        client.VI = temp.VI ? parseInt(temp.VI) : '';
        client.SA = temp.SA ? parseInt(temp.SA) : '';

        client.ORDEN_LU = temp.ORDEN_LU || '';
        client.ORDEN_MA = temp.ORDEN_MA || '';
        client.ORDEN_MI = temp.ORDEN_MI || '';
        client.ORDEN_JU = temp.ORDEN_JU || '';
        client.ORDEN_VI = temp.ORDEN_VI || '';
        client.ORDEN_SA = temp.ORDEN_SA || '';
      });

      this.clients = clients;
    },
    async setVirtualItinerary(day, code, value, ruta) {
      //const ruta = this.formItinerary.rute;
      
      if (day && code && ruta) {
        try {
          const response = await axios.get(`endpoint.php?pass=setVirtualItinerary&day=${day}&code=${code}&virtualSeller=${value}&ruta=${ruta}`);
          if (response.data[0]?.exito === 1) {
            //vue.showToastSwal('success')
          } else {
            this.clearDay(day, code);
            this.showToastSwal('error');
          }
        } catch (error) {
          console.error(error);
        }
      }
    },
    clearDay(day, code) {
      const registry = this.clients.find(client => client.KUNNR === code);
      if (registry) {
        registry[day] = '';
      }
    },
    showToastSwal(type) {
      const Toast = Swal.mixin({
        toast: true,
        position: 'top-right',
        iconColor: 'white',
        customClass: {
          popup: 'colored-toast',
        },
        showConfirmButton: false,
        timer: 1500,
        timerProgressBar: true,
      });

      Toast.fire({
        icon: type,
        title: type === 'success' ? 'Hecho!' : 'Error!',
      });
    },
    orderClients() {
      const day = this.filters.day;
      const field = `ORDEN_${day}`;
      this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';

      if (!day && !this.filters.virtualManager) {
        return;
      }

      this.clients.sort((a, b) => {
        return this.sortOrder === 'asc' ? a[field] - b[field] : b[field] - a[field];
      });
    },
    clearFilters() {
      this.filters.text = '';
      this.filters.virtualManager = '';
      this.filters.day = '';
    },
    // recieves an array of objects and a key, and returns an array of unique values
    getUniqueValues(array, key) {
      return [...new Set(array.map(item => item[key]))];
    },
    dragStart(event, client) {
      event.dataTransfer.setData('client', JSON.stringify(client));
      this.dragClient = client;
    },
    drop(event, client) {
      event.preventDefault();
      this.orderClients();
      this.dropClient = client;

      const day = this.filters.day;
      const field = `ORDEN_${day}`;

      const dragClientIndex = this.clients.findIndex(client => client.KUNNR === this.dragClient.KUNNR);
      const dropClientIndex = this.clients.findIndex(client => client.KUNNR === this.dropClient.KUNNR);

      // move the dragged client to the drop position
      this.clients.splice(dropClientIndex, 0, this.clients.splice(dragClientIndex, 1)[0]);
      
      // update the order of all clients
      this.updateOrderFilteredClients();
      this.getClients();
    },
    updateOrderFilteredClients() {
      const day = this.filters.day;
      const field = `ORDEN_${day}`;

      // Order all filtered clients from 1 to n
      this.filteredClients.forEach((client, index) => {
        client[field] = index + 1;
      });

      this.filteredClients.forEach(client => {
        this.setOrder(client, field);
      });
    },
    async getAllItineraries() {
      try {
        const forCRMBool = this.forCRM ? 1 : 0;
        const response = await axios.get('endpoint.php?pass=getAllItineraries&forCRM=' + forCRMBool);
        return response.data;
      } catch (error) {
        console.error(error);
        return [];
      }
    },
    async guardarEnCRM() {

      try {
        this.loaders.saveCRM = true;
        const allItineraries = await this.getAllItineraries();
        //console.log(allItineraries);
        
        if (!allItineraries.length) {
          swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se encontraron itinerarios para guardar en CRM',
          });
          return;
        }
        const response = await axios.get('postgres.php', {
          params: {
            endpoint: 'guardarEnCRM',
            itineraries: allItineraries,
          }
        });
        
        //console.log(response.data);
        
        if (response.data.exito === 1) {
          this.showToastSwal('success');
        } else {
          this.showToastSwal('error');
        }
        
      } catch (error) {
        console.error(error);
        this.showToastSwal('error');
      } finally {
        this.loaders.saveCRM = false;
      }
      
    },
    debouncedSearch() {
        clearTimeout(this.debounceTimeout);
        this.clientesTemporales = [];
        this.loadingSAPClientes = true;
        if (this.buscarCliente == '') {
            this.loadingSAPClientes = false;
            return;
        }
        this.debounceTimeout = setTimeout(() => {
            this.fetchResults();
        }, 2000); // 5000 ms = 5 segundos
    },
    fetchResults() {
        switch (this.selectorBusquedaSAP) {
            case 'NOMBRE':
                let buscarClienteUpper = this.buscarCliente.toUpperCase();
                const url = this.sap_api + `/tablefs?table=KNA1&where=name1%20like%27%${buscarClienteUpper}%%27%20or%20name2%20like%20%27%${buscarClienteUpper}%%27%20&fields=KUNNR,NAME1,NAME2,SORTL,STRAS`;
                //console.log(url);
                axios.get('curl.php', { params: { url: url } })
                    .then(res => {
                        
                        //this.motivos = this.jclear(res.data);
                        this.clientesTemporales = res.data;
                        this.clientesTemporales = this.clientesTemporales.slice(0, 20);
                        this.loadingSAPClientes = false;
                    })
                    .catch(err => {
                        console.error(err);
                        this.loadingSAPClientes = false;
                    });
                break;
            case 'CODIGO':
                axios.post(this.urlAPI + '/datos-clientes/', {
                        "Clientes": [
                            this.buscarCliente
                        ]
                    }, {
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(res => {
                        this.clientesTemporales = this.jclear(res.data);
                        this.loadingSAPClientes = false;
                    })
                    .catch(err => {
                        console.error(err);
                        this.loadingSAPClientes = false;
                    });
                break;
            case 'RUTA':
                axios.post('/api/getdatossapruta', {
                        "nombre_cliente": this.buscarCliente,
                        "ruta": this.rutaSeleccionada,
                    }, {
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(res => {
                        this.clientesTemporales = res.data;
                        this.loadingSAPClientes = false;
                    })
                    .catch(err => {
                        console.error(err);
                        this.loadingSAPClientes = false;
                    });
                break;


            default:
                break;
        }

    },
    jclear(d) {
      return JSON.parse(JSON.stringify(d));
    },
    predetalle(result) {
      let existeCliente = this.clients.find(r => r.KUNNR == result.KUNNR)
      if (!existeCliente) {

          // Add days and order to result
          result.PREVENDEDOR = '';
          result.LU = '';
          result.MA = '';
          result.MI = '';
          result.JU = '';
          result.VI = '';
          result.SA = '';
          result.ORDEN_LU = '';
          result.ORDEN_MA = '';
          result.ORDEN_MI = '';
          result.ORDEN_JU = '';
          result.ORDEN_VI = '';
          result.ORDEN_SA = '';

          if (this.filters.virtualManager){
            result.PREVENDEDOR = this.filters.virtualManager;
          }

          if (this.filters.day){
            result[this.filters.day] = 1;
          }

          this.clients.push(result);
          
          if (this.filters.day){
            this.setVirtualItinerary('PREVENDEDOR', result.KUNNR, this.filters.virtualManager, result.SORTL);
            this.setVirtualItinerary(this.filters.day, result.KUNNR, 1, result.SORTL);
          } else {
            this.setVirtualItinerary('PREVENDEDOR', result.KUNNR, this.filters.virtualManager, result.SORTL);
          }
          
      }
      
    },
  },
});
