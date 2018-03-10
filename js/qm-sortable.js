//
// Плагин сортировки
// tnx https://learn.javascript.ru/drag-and-drop-objects
// 


var DragManager = new function() {

  /**
   * составной объект для хранения информации о переносе:
   * {
   *   elem - элемент, на котором была зажата мышь
   *   avatar - аватар
   *   downX/downY - координаты, на которых был mousedown
   *   shiftX/shiftY - относительный сдвиг курсора от угла элемента
   * }
   */

  var dragObject = {};

  var self = this;

  function onMouseDown(e) {

    if (e.which != 1) return;

    var elem = e.target.closest('.qm-draggable.active');
    if (!elem) return;

    elem.classList.add('move-process');
    
    dragObject.elem = elem;
    
    // запомним, что элемент нажат на текущих координатах pageX/pageY
    dragObject.downX = e.pageX;
    dragObject.downY = e.pageY;
    
    // запомним id вопроса, если он есть
    var box = elem.closest('.question');
    if (box) var boxId = box.getAttribute('id');
    if (boxId) dragObject.boxId = boxId

    return false;
  }

  function onMouseMove(e) {
    if (!dragObject.elem) return; // элемент не зажат

    if (!dragObject.avatar) { // если перенос не начат...
      var moveX = e.pageX - dragObject.downX;
      var moveY = e.pageY - dragObject.downY;

      // если мышь передвинулась в нажатом состоянии недостаточно далеко
      if (Math.abs(moveX) < 3 && Math.abs(moveY) < 3) {
        return;
      }

      // начинаем перенос
      dragObject.avatar = createAvatar(e); // создать аватар
      if (!dragObject.avatar) { // отмена переноса, нельзя "захватить" за эту часть элемента
        dragObject = {};
        return;
      }

      // аватар создан успешно
      // создать вспомогательные свойства shiftX/shiftY
      var coords = getCoords(dragObject.elem);

      dragObject.shiftX = dragObject.downX - coords.left;
      dragObject.shiftY = dragObject.downY - coords.top;

      startDrag(e); // отобразить начало переноса

    }
   
    var targetElem = findDroppable(e);


    var parent = dragObject.elem.closest('.qm-sortable')
    if (parent) child = parent.querySelector('.target');
    if (child) child.classList.remove('target');
    if (targetElem) targetElem.classList.add('target');

    // отобразить перенос объекта при каждом движении мыши
    dragObject.avatar.style.left = e.pageX - dragObject.shiftX + 'px';
    dragObject.avatar.style.top = e.pageY - dragObject.shiftY + 'px';

    return false;
  }

  function onMouseUp(e) {

    var elements = document.querySelectorAll('.move-process');
    for (var i = 0; i < elements.length; i++) elements[i].classList.remove('move-process');


    if (dragObject.avatar) { // если перенос идет
      // finishDrag(e);

        var targetElem = findDroppable(e);
        
        // Удалить аватар
        var elem = dragObject.avatar.parentNode;
        if (elem) elem.removeChild(dragObject.avatar);
        dragObject.elem.classList.remove('reflexion');
        
        elem = dragObject.elem.closest('table').querySelector('.target');
        if (elem) elem.classList.remove('target');

        // Отпустили мышь не на месте приземления
        if (!targetElem) {
            dragObject = {};
            return;
        }
        
        // Поменять элементы местами
        var box1 = dragObject.elem.parentNode;
        var box2 = targetElem.parentNode;;
        
        if (!box1) return;
        if (!box2) return;
        
        box1.appendChild(targetElem);
        box2.appendChild(dragObject.elem);
        
        // Анимация приземления

        dragObject.elem.classList.add('landed');
        setTimeout( function( elem ){ elem.classList.remove('landed') }, 300, dragObject.elem );

        // Записать данные выбора

        var table = box1.closest('table');
        var elements = table.querySelectorAll('tr');

        for (var i = 0; i < elements.length; i++) {

            var input = elements[i].querySelector('input[type=hidden]');
            var mover = elements[i].querySelector('.mover');
            var linker = elements[i].querySelector('.linker');
            var data = mover.getAttribute('data-caption');
            input.setAttribute('value',data);
            linker.classList.add('checked'); 
        }

        }

        // перенос либо не начинался, либо завершился
        // в любом случае очистим "состояние переноса" dragObject
        dragObject = {};
  }

  
  function createAvatar(e) {

    var avatar = dragObject.elem.cloneNode(true);
    dragObject.elem.classList.add('reflexion');

   
    // функция для отмены переноса
    avatar.rollback = function() {
      avatar.remove();
      dragObject.elem.classList.remove('reflexion');
    };

    avatar.style.width = dragObject.elem.clientWidth + 'px';
    avatar.style.height = dragObject.elem.clientHeight + 'px';
    avatar.classList.add( 'avatar' );

    return avatar;
  }

  function startDrag(e) {
    var avatar = dragObject.avatar;

    // инициировать начало переноса
    document.body.appendChild(avatar);
    avatar.style.zIndex = 9999;
    avatar.style.position = 'absolute';
  }

  function findDroppable(event) {
    // спрячем переносимый элемент
    dragObject.avatar.hidden = true;

    // получить самый вложенный элемент под курсором мыши
    var elem = document.elementFromPoint(event.clientX, event.clientY);
    // показать переносимый элемент обратно
    dragObject.avatar.hidden = false;
    
    if (elem == null) {
      // такое возможно, если курсор мыши "вылетел" за границу окна
      return null;
    }

    var box = elem.closest('.question');
    if (box) var boxId = box.getAttribute('id');
    if (boxId) {
      if (boxId==dragObject.boxId)  return elem.closest('.qm-draggable');
    }

    return null;
    // return elem.closest('.qm-draggable');
  }

  document.onmousemove = onMouseMove;
  document.onmouseup = onMouseUp;
  document.onmousedown = onMouseDown;

};


function getCoords(elem) { // кроме IE8-
  var box = elem.getBoundingClientRect();

  return {
    top: box.top + pageYOffset,
    left: box.left + pageXOffset
  };

}