%module chmlib

%{
#include <chm_lib.h>
%}


#ifdef WIN32
typedef unsigned __int64 LONGUINT64;
typedef __int64          LONGINT64;
#else
typedef unsigned long long LONGUINT64;
typedef long long          LONGINT64;
#endif

#define CHM_UNCOMPRESSED (0)
#define CHM_COMPRESSED   (1)


%typemap(memberin) char [ANY] {
	strncpy($1, $input, $1_dim0);
}
%typemap(out,fragment="SWIG_FromCharPtr") char [ANY] { 
	$result = SWIG_FromCharPtr($1);
}

struct chmUnitInfo
{
    LONGUINT64         start;
    LONGUINT64         length;
    int                space;
    char               path[CHM_MAX_PATHLEN+1];
};

struct chmFile* chm_open(const char *filename);

void chm_close(struct chmFile *h);
		
#define CHM_PARAM_MAX_BLOCKS_CACHED 0

void chm_set_param(struct chmFile *h, int paramType, int paramVal);

%typemap(in, numinputs=0) struct chmUnitInfo *ui {
	$1 = (struct chmUnitInfo *) calloc(1, sizeof(struct chmUnitInfo));
}

%typemap(argout) struct chmUnitInfo *ui {
	Py_XDECREF($result);	/* Blow away any previous result */
	printf("%i", result);
	if (result != CHM_RESOLVE_SUCCESS) {
		Py_INCREF(Py_None); 
		return Py_None;
	}
	$result = SWIG_NewPointerObj((void *) $1, $1_descriptor, 1);
}



int chm_resolve_object(struct chmFile *h,
                       const char *objPath,
                       struct chmUnitInfo *ui);


/*
%rename(chm_resolve_object) python_chm_resolve_object;

%inline %{

PyObject *python_chm_resolve_object(struct chmFile *h, const char *objPath) {
	struct chmUnitInfo *ui;

	ui = (struct chmUnitInfo *) calloc(1, sizeof(struct chmUnitInfo));

	if(chm_resolve_object(h, objPath, ui) != CHM_RESOLVE_SUCCESS) {
		free(ui);
		Py_INCREF(Py_None); 
		return Py_None;
	}
	
	return SWIG_NewPointerObj((void *) ui, SWIGTYPE_p_chmUnitInfo, 1);
}

%}
*/

/*
LONGINT64 chm_retrieve_object(struct chmFile *h,
                              struct chmUnitInfo *ui,
                              unsigned char *buf,
                              LONGUINT64 addr,
                              LONGINT64 len);
*/

%rename(chm_retrieve_object) python_chm_retrieve_object;

%inline %{

PyObject *python_chm_retrieve_object(struct chmFile *h, struct chmUnitInfo *ui, LONGUINT64 addr, LONGINT64 len)
{
	PyObject *bufobj;
	unsigned char *buf;

	if(!(bufobj = PyString_FromStringAndSize(NULL, len)))
		return NULL;
	
	buf = PyString_AsString(bufobj);
	len = chm_retrieve_object(h, ui, buf, addr, len);

	_PyString_Resize(&bufobj, len);

	return bufobj;
}

%}

#define CHM_ENUMERATE_NORMAL    (1)
#define CHM_ENUMERATE_META      (2)
#define CHM_ENUMERATE_SPECIAL   (4)
#define CHM_ENUMERATE_FILES     (8)
#define CHM_ENUMERATE_DIRS      (16)
#define CHM_ENUMERATE_ALL       (31)
#define CHM_ENUMERATOR_FAILURE  (0)
#define CHM_ENUMERATOR_CONTINUE (1)
#define CHM_ENUMERATOR_SUCCESS  (2)

%inline %{

struct python_context {
	PyObject *eobj;
	PyObject *contextobj;
};

int python_enumerator(struct chmFile *h, struct chmUnitInfo *ui, void *context) {
	struct python_context *ctx = (struct python_context *)context;
	PyObject *hobj, *uiobj, *resultobj;
	int result;
	
	hobj = SWIG_NewPointerObj((void *) h, SWIGTYPE_p_chmFile, 0);
	uiobj = SWIG_NewPointerObj((void *) ui, SWIGTYPE_p_chmUnitInfo, 0);
	resultobj = PyObject_CallFunction(ctx->eobj, "OOO", hobj, uiobj, ctx->contextobj);
	if (!resultobj)
		goto fail;
	result = PyInt_AsLong(resultobj);
	if (PyErr_Occurred())
		goto fail;
	return result;
fail:
	return CHM_ENUMERATOR_FAILURE;
}

%}

%rename(chm_enumerate) python_chm_enumerate;
%rename(chm_enumerate_dir) python_chm_enumerate_dir;

%inline %{

PyObject *python_chm_enumerate(struct chmFile *h, int what, PyObject *eobj, PyObject *contextobj)
{
	struct python_context ctx;
	ctx.eobj = eobj;
	ctx.contextobj = contextobj;
	int result;
	
	result = chm_enumerate(h, what, python_enumerator, &ctx);
	if (PyErr_Occurred())
		goto fail;
	return PyInt_FromLong((long)result);
fail:
	return NULL;
}

PyObject *python_chm_enumerate_dir(struct chmFile *h, const char *prefix, int what, PyObject *eobj, PyObject *contextobj)
{
	struct python_context ctx;
	ctx.eobj = eobj;
	ctx.contextobj = contextobj;
	int result;

	result = chm_enumerate_dir(h, prefix, what, python_enumerator, &ctx);
	if (PyErr_Occurred())
		goto fail;
	return PyInt_FromLong((long)result);
fail:
	return NULL;
}

%}
