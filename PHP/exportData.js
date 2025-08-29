const EXCEL_TYPE =
  "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=UTF-8";
const EXCEL_EXTENSION = ".xlsx";

function downloadAsExcel(data, cartera, asesor) {
  const worksheet = XLSX.utils.json_to_sheet(data);
  const workbook = {
    Sheets: {
      data: worksheet,
    },
    SheetNames: ["data"],
  };
  const excelBuffer = XLSX.write(workbook, { bookType: "xlsx", type: "array" });

  const asesorData = asesor.split(",");
  const apellidos = asesorData[0].split(" ");
  const nombres = asesorData[1].trimStart().split(" ");
  const asesorName = nombres[0] + "_" + apellidos[0];
  saveAsExcel(excelBuffer, `${cartera}_${asesorName}`);
}

function saveAsExcel(buffer, filename) {
  const data = new Blob([buffer], { type: EXCEL_TYPE });
  saveAs(data, filename + EXCEL_EXTENSION);
}
