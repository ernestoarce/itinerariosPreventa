var vue = new Vue({
  el: '#content',
  data: {
    showAllColumns: false,
    virtualSellers: [],
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
    sap_api: '',
  },
  mounted() {
    this.getVirtualSellers();
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
    async getVirtualSellers() {
      try {
        const response = await axios.get('endpoint.php?pass=getVirtualSellers');
        this.virtualSellers = response.data[0];
        this.sap_api = this.sap_api || response.data[1];
      } catch (error) {
        console.error(error);
      }
    },
    async getClients() {
      this.clients = [];
      const ruta = this.formItinerary.rute;
      const api = this.sap_api;
      let url = '';

      if (ruta === 'TODOS') {
        url = `${api}/tablefs?table=KNA1&where=SORTL%20LIKE%20%27DET%%27OR%20SORTL%20LIKE%20%27DSM%%27&fields=KUNNR,NAME1,NAME2,SORTL,LZONE,STRAS,TELF1`;
      } else {
        url = `${api}/tablefs?table=KNA1&where=SORTL%20=%20%27${ruta}%27&fields=KUNNR,NAME1,NAME2,SORTL,LZONE,STRAS,TELF1`;
      }

      if (ruta) {
        this.loaders.list = true;
        try {
          const response = await axios.get(url);
          this.loaders.list = false;
          this.getVirtualItinerary(response.data);
        } catch (error) {
          this.loaders.list = false;
          console.error(error);
        }
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
        const temp = itinerary?.find(virtual => virtual.CODIGO === client.KUNNR) || {};

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
    async setVirtualItinerary(day, code, value) {
      const ruta = this.formItinerary.rute;
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
  },
});
