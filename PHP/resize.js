let img1 = document.getElementById("imagen1");
let img2 = document.getElementById("imagen2");
let img3 = document.getElementById("imagen3");
let form = document.getElementById("agregar-gestion-form");
let formData = null;

let btnAdd = document.getElementById("btn-add-gestion");

WIDTH = 800;

const megasAllowed = 1;

// const compressImage = (input, keyName) => {
//     input.addEventListener("change", (event) => {
//         let image_file = event.target.files[0];

//         // JUST APPLY TO FILES WITH SIZE > 1MB
//         // if (image_file.size > megasAllowed * 1048576) {
//         //     alert(`Error, archivo mayor a ${megasAllowed}MB`);
//         //     event.target.value = "";
//         //     return
//         // }
//         if (!formData) {
//         formData = new FormData(form);
//         }

//         // JUST COMPRESS FILES WITH SIZE HIGHER THAN
//         if (image_file.size > megasAllowed * 1048576) {
//             console.log('Es mayor, comprimiendo')
//             let reader = new FileReader();

//             reader.readAsDataURL(image_file);

//             reader.onload = (event) => {

//                 image_url = event.target.result;
//                 let image = document.createElement('img');
//                 image.src = image_url;

//                 image.onload = (e) => {

//                     let canvas = document.createElement('canvas');
//                     let ratio = WIDTH / image.width;
//                     canvas.width = WIDTH;
//                     canvas.height = image.height * ratio;

//                     let context = canvas.getContext('2d');
//                     context.drawImage(image, 0, 0, canvas.width, canvas.height);

//                     let new_image_url = canvas.toDataURL('image/jpeg', 98)

//                     let image_file2 = urlToFile(new_image_url)

//                     formData.set(keyName, image_file2);

//                     for (const i of formData) {
//                         console.log(i[0], i[1])
//                     }
//                 }
//             }

//         }else {
//             // set the image_file without the compress changes
//             console.log('No se comprimirá imagen')
//             formData.set(keyName, image_file);
//             for (const i of formData) {
//                 console.log(i[0], i[1])
//             }
//         }

//     })
// }

const compressImage = (input, keyName) => {
  input.addEventListener("change", (event) => {
    let image_file = event.target.files[0];

    // JUST APPLY TO FILES WITH SIZE > 1MB
    // if (image_file.size > megasAllowed * 1048576) {
    //     alert(`Error, archivo mayor a ${megasAllowed}MB`);
    //     event.target.value = "";
    //     return
    // }

    // JUST COMPRESS FILES WITH SIZE HIGHER THAN

    let reader = new FileReader();

    reader.readAsDataURL(image_file);

    reader.onload = (event) => {
      image_url = event.target.result;
      let image = document.createElement("img");
      image.src = image_url;

      image.onload = (e) => {
        let canvas = document.createElement("canvas");
        let ratio = WIDTH / image.width;
        canvas.width = WIDTH;
        canvas.height = image.height * ratio;

        let context = canvas.getContext("2d");
        context.drawImage(image, 0, 0, canvas.width, canvas.height);

        let new_image_url = canvas.toDataURL("image/jpeg", 60);

        let image_file = urlToFile(new_image_url);

        if (!formData) {
          formData = new FormData(form);
        }

        formData.set(keyName, image_file);

        // for (const i of formData) {
        //     console.log(i[0], i[1])
        // }
      };
    };
  });
};

let urlToFile = (url) => {
  let arr = url.split(",");
  // console.log(arr)
  let mime = arr[0].match(/:(.*?);/)[1];
  let data = arr[1];

  let dataStr = atob(data);
  let n = dataStr.length;
  let dataArr = new Uint8Array(n);

  while (n--) {
    dataArr[n] = dataStr.charCodeAt(n);
  }

  let file = new File([dataArr], "File.jpg", { type: mime });

  return file;
};

const imagesArray = [
  {
    value: img1,
    text: "imagen1",
  },
  {
    value: img2,
    text: "imagen2",
  },
  {
    value: img3,
    text: "imagen3",
  },
];

imagesArray.forEach((e) => {
  const currentFileLength = e.value.files.length;
  // if (currentFileLength != 0) {
  compressImage(e.value, e.text);
  // }
});

// EventListener de envío fuera de la función compressImage
form.addEventListener("submit", (e) => {
  e.preventDefault();

  if (!formData) {
    formData = new FormData(form);
  }

  // Validar que hay ubicación

  // if (img1.files.length === 0) {
  //     alert('Imagen no seleccionada, por favor seleccione al menos 1');
  //     btnAdd.disabled = false;
  //     return
  // }

  // console.log formData
  // for (let entry of formData.entries()) {
  //     console.log(entry[0], entry[1]);
  // }

  // for (const i of formData) {
  // coords
  let latitude = document.getElementById("latitud");
  let longitude = document.getElementById("longitud");

  if (latitude.value.length === 0 || longitude.value.length === 0) {
    alert(
      "Ubicación no detectada, activarla y actualizar la página para grabar gestión"
    );
    return;
  }

  // else if ((i[0] === 'imagen1') && i[1] === '') {
  //     alert('Ubicación no detectada, activarla y actualizar la página para grabar gestión')
  //     return
  // }

  // Enviar el FormData del formulario al servidor
  fetch(form.action, {
    method: "POST",
    body: formData,
  })
    .then((res) => {
      return res.json();
    })
    .then((data) => {
      // console.log(data)
      if (data.success) {
        // btnAdd.disabled = true;
        alert(data.message);
        window.history.go(-1);
      } else {
        console.error(data.message);
        alert(data.message);
        // alert('Error al insertar registro, probar nuevamente');
        location.reload();
      }
    })
    .catch((err) => {
      console.log(err);
      alert("Error, complete todos los campos y en orden nuevamente");
      location.reload();
    });
});

/********************************** NEW WAY *********************************/
