<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Itinerario</title>
  <link rel="stylesheet" href="resources/bootstrap/bootstrap.css">
  <link rel="stylesheet" href="resources/bootstrap/bootstrap-icons.css">
  <link rel="stylesheet" href="resources/toast.css">
</head>
<body>
  <div id="content" v-cloak class="p-4">
    <div class="row mb-3">

      <div class="col-2">
        <div class="input-group">
          <label class="input-group-text">ENTREGADOR:</label>
          <select class="form-select" v-model="formItinerary.rute" @change="getClients()">
            <option>TODOS</option>
            <option v-for="v in virtualSellers">{{ v.TOUR_ID }}</option>
          </select>
        </div>
      </div>

      <div class="col">
        <div class="input-group">
          <label class="input-group-text">FILTRAR:</label>
          <input v-model="filters.text" type="text" class="form-control" placeholder="CODIGO/NOMBRE/NOM-COMERCIAL/RUTA">
        </div>
      </div>

      <div class="col">
        <div class="input-group">
          <label class="input-group-text">PREVENDEDOR:</label>
          <select class="form-select" v-model="filters.virtualManager">
            <option selected></option>
            <option v-for="v in virtualSellers">{{ v.ID }}</option>
          </select>
        </div>
      </div>

      <div class="col-2">
        <div class="input-group">
          <label class="input-group-text">DÍA:</label>
          <select class="form-select" v-model="filters.day">
            <option selected></option>
            <option>LU</option>
            <option>MA</option>
            <option>MI</option>
            <option>JU</option>
            <option>VI</option>
            <option>SA</option>
          </select>
        </div>
      </div>

    </div>

    <div class="row">
      <div class="col text-muted">
        <button class="btn btn-sm btn-primary" @click="orderClients()" :disabled="filters.day == '' || filters.virtualManager == '' || formItinerary.rute == 'TODOS' || formItinerary.rute == ''">
          ORDENAR
          <i :class="sortOrder == '' ? 'bi bi-list text-white' : sortOrder == 'asc' ? 'bi bi-sort-alpha-down text-white' : 'bi bi-sort-alpha-up text-white'"></i>
        </button>
        **Debe filtrar por DÍA y PREVENDOR y ENTREGADOR para poder ordenar**
      </div>
      <div class="col text-end mb-3 fw-bold">Registros: {{ filteredClients.length }}</div>
    </div>
    
  
    <div class="row px-2">
    
      <div class="col-12 px-1">
        
        <table class="table table-striped table-bordered table-sm mb-0">
          <thead class="table" style="background-color:#0a58ca; color:white;">
            <tr class="py-0">
              <th scope="col" :colspan="showAllColumns ? 8 : 6" class="text-center py-0">
                CLIENTES DE RUTAS DE PREVENTA
              </th>
              <th scope="col" colspan="6" class="text-center py-0">
                DÍAS Y RUTA VIRTUAL QUE LE ATENDERÁ
              </th>
            </tr>
            <tr>
                <th scope="col" class="text-center py-0">
                  ORDEN
                    <button class="btn btn-sm p-0" @click="orderClients()">
                      <i :class="sortOrder == '' ? 'bi bi-list text-white' : sortOrder == 'asc' ? 'bi bi-sort-alpha-down text-white' : 'bi bi-sort-alpha-up text-white'"></i>
                    </button>
                </th>
              <th scope="col" class="text-center py-0">CODIGO</th>
              <th scope="col" class="text-center py-0">NOMBRE</th>
              <th scope="col" class="text-center py-0">NOM-COMERCIAL</th>
              <th scope="col" class="text-center py-0">RUTA</th>
              <th scope="col" v-show="showAllColumns" class="text-center py-0">DIRECCION</th>
              <th scope="col" v-show="showAllColumns" class="text-center py-0">TELEFONO</th>
              <th scope="col" class="text-center py-0" style="width:5px;">
                <button class="btn btn-sm p-0" @click="showAllColumns = !showAllColumns">
                  <i :class="showAllColumns ? 'bi bi-caret-left-fill text-white' : 'bi bi-caret-right-fill text-white'"></i>
                </button>
              </th>
              <th scope="col" class="text-center py-0">LU ({{ totals.lu }})</th>
              <th scope="col" class="text-center py-0">MA ({{ totals.ma }})</th>
              <th scope="col" class="text-center py-0">MI ({{ totals.mi }})</th>
              <th scope="col" class="text-center py-0">JU ({{ totals.ju }})</th>
              <th scope="col" class="text-center py-0">VI ({{ totals.vi }})</th>
              <th scope="col" class="text-center py-0">SA ({{ totals.sa }})</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(i, n) in filteredClients">
              <td>
                <select v-if="filters.day != '' && filters.virtualManager != '' && formItinerary.rute != 'TODOS' && formItinerary.rute != ''"
                @change="setOrder(i, 'ORDEN_' + filters.day)"
                class="py-0 form-select form-select-sm" v-model="i['ORDEN_' + filters.day]">
                  <option value="0"></option>
                  <option v-for="v in filteredClients.length" :key="v">{{ v }}</option>
                </select>
              </td>
              <td class="text-center">{{ i.KUNNR }}</td>
              <td>{{ i.NAME1 }}</td>
              <td>{{ i.NAME2 }}</td>
              <td class="text-center">{{ i.SORTL }}</td>
              <td v-show="showAllColumns">{{ i.STRAS }}</td>
              <td v-show="showAllColumns" class="text-center">{{ i.TELF1 }}</td>
              <td></td>
              <td class="text-center" style="height:25px; width: 110px;">
                <select @change="setVirtualItinerary('LU', i.KUNNR, i.LU)"  class="py-0 form-select form-select-sm" v-model="i.LU">
                  <option></option>
                  <option v-for="v in virtualSellers">{{ v.ID }}</option>
                </select>
              </td>
              <td class="text-center" style="height:25px; width: 110px;">
                <select @change="setVirtualItinerary('MA', i.KUNNR, i.MA)"  class="py-0 form-select form-select-sm" v-model="i.MA">
                  <option></option>
                  <option v-for="v in virtualSellers">{{ v.ID }}</option>
                </select>
              </td>
              <td class="text-center" style="height:25px; width: 110px;">
                <select @change="setVirtualItinerary('MI', i.KUNNR, i.MI)"  class="py-0 form-select form-select-sm" v-model="i.MI">
                  <option></option>
                  <option v-for="v in virtualSellers">{{ v.ID }}</option>
                </select>
              </td>
              <td class="text-center" style="height:25px; width: 110px;">
                <select @change="setVirtualItinerary('JU', i.KUNNR, i.JU)"  class="py-0 form-select form-select-sm" v-model="i.JU">
                  <option></option>
                  <option v-for="v in virtualSellers">{{ v.ID }}</option>
                </select>
              </td>
              <td class="text-center" style="height:25px; width: 110px;">
                <select @change="setVirtualItinerary('VI', i.KUNNR, i.VI)"  class="py-0 form-select form-select-sm" v-model="i.VI">
                  <option></option>
                  <option v-for="v in virtualSellers">{{ v.ID }}</option>
                </select>
              </td>
              <td class="text-center" style="height:25px; width: 110px;">
                <select @change="setVirtualItinerary('SA', i.KUNNR, i.SA)"  class="py-0 form-select form-select-sm" v-model="i.SA">
                  <option></option>
                  <option v-for="v in virtualSellers">{{ v.ID }}</option>
                </select>
              </td>
            </tr>
            <tr>
              <td :colspan="showAllColumns ? 8 : 6"
              class="text-center py-0">TOTALES
              </td>
              <td class="text-center py-0">{{ totals.lu }}</td>
              <td class="text-center py-0">{{ totals.ma }}</td>
              <td class="text-center py-0">{{ totals.mi }}</td>
              <td class="text-center py-0">{{ totals.ju }}</td>
              <td class="text-center py-0">{{ totals.vi }}</td>
              <td class="text-center py-0">{{ totals.sa }}</td>

            </tr>
          </tbody>
        </table>
        <div v-show="loaders.list" colspan="7"
        class="text-center m-2">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <div v-show="filteredClients.length == 0 && !loaders.list" class="alert alert-light text-center" role="alert">
          No se encontraron resultados
        </div>
      </div>
    </div>

  </div>

</body>
<script src="resources/sw2.min.js"></script>
<script src="resources/vue26.js"></script>
<script src="resources/axios.min.js"></script>
<!-- <script src="resources/sweetalert.min.js"></script> -->
<script src="index.js?t=<?= time(); ?>"></script>
<script src="resources/bootstrap.min.js"></script>
</html>