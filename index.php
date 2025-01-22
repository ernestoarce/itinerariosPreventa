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

      <div class="col d-flex justify-content-end">
        <div class="input-group" style="width: 300px;">
          <label class="input-group-text">ENTREGADOR:</label>
          <select class="form-select" v-model="formItinerary.rute" @change="getClients()">
            <option>TODOS</option>
            <option v-for="v in virtualSellers">{{ v.TOUR_ID }}</option>
          </select>
        </div>
      </div>

      <div class="col d-flex justify-content-center">
        <div class="input-group" style="width: 400px;">
          <label class="input-group-text">PREVENDEDOR:</label>
          <select class="form-select" v-model="filters.virtualManager">
            <option selected></option>
            <option v-for="v in virtualSellers">{{ v.ID }}</option>
          </select>
        </div>
      </div>

      <div class="col d-flex justify-content-start">
        <div class="input-group" style="width: 300px;">
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
      <div class="col">
        <div class="input-group" style="width: 400px;">
          <input v-model="filters.text" type="text" class="form-control" placeholder="Filtrar por: CODIGO/NOMBRE/NOM-COMERCIAL/RUTA">
        </div>
      </div>
      <div class="col text-center" style="min-width: 700px;">
        <button class="btn btn-sm btn-primary mx-2" disabled>
          <i class="me-2 bi bi-geo-alt"></i>
          ORDENAR POR DISTANCIA</button>
        <button class="btn btn-sm btn-danger mx-2" disabled>
          <i class="me-2 bi bi-save"></i>
          GRABAR SECUENCIA</button>
        <button class="btn btn-sm btn-success mx-2" disabled>
          <i class="me-2 bi bi-file-earmark-bar-graph"></i>
          GRAFICAR</button>
      </div>
      <div class="col text-end mb-3 fw-bold">Registros: {{ filteredClients.length }}</div>
    </div>
    
  
    <div class="row px-2">
    
      <div class="col-12 px-1">
        
        <table class="table table-striped table-bordered table-sm mb-0">
          <thead class="table" style="background-color:#0a58ca; color:white;">
            <tr class="py-0">
              <th scope="col" :colspan="showAllColumns ? 11 : 9" class="text-center py-0">
                CLIENTES DE RUTAS DE PREVENTA
              </th>
              <th scope="col" colspan="7" class="text-center py-0">
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
              <th scope="col" class="text-center py-0">HORA DE VISITA</th>
              <th scope="col" class="text-center py-0">TIEMPO DE VISITA</th>
              <th scope="col" class="text-center py-0">TIEMPO DE TRASLADO</th>
              <th scope="col" v-show="showAllColumns" class="text-center py-0">DIRECCION</th>
              <th scope="col" v-show="showAllColumns" class="text-center py-0">TELEFONO</th>
              <th scope="col" class="text-center py-0" style="width:5px;">
                <button class="btn btn-sm p-0" @click="showAllColumns = !showAllColumns">
                  <i :class="showAllColumns ? 'bi bi-caret-left-fill text-white' : 'bi bi-caret-right-fill text-white'"></i>
                </button>
              </th>
              <th scope="col" class="text-center py-0">PREVENDEDOR</th>
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
              <td class="text-center"></td>
              <td class="text-center"></td>
              <td class="text-center"></td>
              <td v-show="showAllColumns">{{ i.STRAS }}</td>
              <td v-show="showAllColumns" class="text-center">{{ i.TELF1 }}</td>
              <td></td>
              <td class="text-center">
                <select class="py-0 form-select form-select-sm" v-model="i.PREVENDEDOR" @change="setVirtualItinerary('PREVENDEDOR', i.KUNNR, i.PREVENDEDOR)">
                  <option></option>
                  <option v-for="v in virtualSellers">{{ v.ID }}</option>
                </select>
              </td>
              <td class="text-center">
                <input type="checkbox" @change="setVirtualItinerary('LU', i.KUNNR, i.LU)" v-model="i.LU" :disabled="!i.PREVENDEDOR" />
              </td>
              <td class="text-center">
                <input type="checkbox" @change="setVirtualItinerary('MA', i.KUNNR, i.MA)" v-model="i.MA" :disabled="!i.PREVENDEDOR" />
              </td>
              <td class="text-center">
                <input type="checkbox" @change="setVirtualItinerary('MI', i.KUNNR, i.MI)" v-model="i.MI" :disabled="!i.PREVENDEDOR" />
              </td>
              <td class="text-center">
                <input type="checkbox" @change="setVirtualItinerary('JU', i.KUNNR, i.JU)" v-model="i.JU" :disabled="!i.PREVENDEDOR" />
              </td>
              <td class="text-center">
                <input type="checkbox" @change="setVirtualItinerary('VI', i.KUNNR, i.VI)" v-model="i.VI" :disabled="!i.PREVENDEDOR" />
              </td>
              <td class="text-center">
                <input type="checkbox" @change="setVirtualItinerary('SA', i.KUNNR, i.SA)" v-model="i.SA" :disabled="!i.PREVENDEDOR" />
              </td>
            </tr>
            <tr v-if="filteredClients.length > 0">
              <td :colspan="showAllColumns ? 12 : 10"
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
        <div v-show="loaders.list" colspan="8"
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